<?php
namespace App\Service\AI;

use App\Service\AccountService;
use App\Service\AI\DTO\ImageInterface;
use App\Service\AI\DTO\OpenAI\Chat\ChatDTO;
use App\Service\AI\DTO\OpenAI\ImageDTO;
use App\Service\AI\DTO\OpenAI\TextDTO;
use App\Service\AI\DTO\TextInterface;
use App\Util\AITypeEnum;
use App\Util\Helper;
use App\Util\TypeDataEnum;
use Exception;
use Orhanerday\OpenAi\OpenAi;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class OpenAIService implements AIInterface
{

    public const DIMENSION = 1000;
    public const TRANSPORT_NAME = 'rewrite';
    public const ADDITIONAL_COUNT_TOKEN = 200;
    private const CACHE_TIME = 604800;
    private const RICH_LIMIT_ERROR_TYPE = 'tokens';
    private const INVALID_REQUEST = 'invalid_request_error';

    public function __construct(
        private int $maxToken,
        private LoggerInterface $logger,
        private OpenAi $openAI,
        private CacheInterface $cache,
        private SerializerInterface $serializer,
        private int $countImage
    )
    {
        
    }

    public function rewrite(mixed $idt, string $textRewrite, string $langOriginal, string $translateTo = '', string $type = ''): TextInterface
    {
        $modificator = match ($type) {
            AITypeEnum::TAG_AI->value => ', text must be formatted with html tags: <p> <span>',
            AITypeEnum::TAG_DEFAULT->value => ', keep existing html tags',
            AITypeEnum::TAG_NOT_USE->value => '',
            AITypeEnum::SHORT_VERSION->value => ', no more than one line',
            default => ', text must be formatted with html tags: ' . $type,
        };

        return $this->cache->get(Helper::generateHash(__METHOD__, [$idt, $textRewrite, $translateTo, $modificator]),
                function (ItemInterface $item) use ($textRewrite, $langOriginal, $translateTo, $modificator) {
                    $item->expiresAfter(self::CACHE_TIME);
                    $system = 'Rewrite on ' . $langOriginal . ($translateTo ? ', translate into ' . $translateTo : '') . ($modificator ? $modificator : '') . '.';

                    return $this->chat($textRewrite, $system);
                });
    }

    public function keywords(mixed $idt, string $title, int $count = 6): TextInterface
    {
        return $this->cache->get(Helper::generateHash(__METHOD__, [$idt, $title, $count]),
                function (ItemInterface $item) use ($title, $count) {
                    $item->expiresAfter(self::CACHE_TIME);

                    return $this->chat($title, "Make $count main keywords, make in one string over a comma: ");
                });
    }

    public function translate(mixed $idt, string $text, string $lang): TextInterface
    {
        return $this->cache->get(Helper::generateHash(__METHOD__, [$idt, $text, $lang]),
                function (ItemInterface $item) use ($text, $lang) {
                    $item->expiresAfter(self::CACHE_TIME);

                    return $this->chat($text, "Translate to $lang, leave tags: ");
                });
    }

    public function createImage(mixed $idt, string $prompt, string $type = 'url'): ImageInterface
    {
        return $this->cache->get(Helper::generateHash(__METHOD__, [$idt, $prompt, $type]),
                function (ItemInterface $item) use ($prompt, $type) {
                    $item->expiresAfter(self::CACHE_TIME);

                    return $this->image('Draw a picture using the following keywords: ' . $prompt, $type);
                });
    }

    public function editImage(mixed $idt, string $prompt, string $imagePath, string $type = 'url'): ImageInterface
    {
        return $this->cache->get(Helper::generateHash(__METHOD__, [$idt, $prompt, $imagePath, $type]),
                function (ItemInterface $item) use ($prompt, $imagePath, $type) {
                    $item->expiresAfter(self::CACHE_TIME);

                    return $this->image($prompt, $type, $imagePath);
                });
    }

    public function findSupposedCost(TypeDataEnum $type, mixed $token): int
    {
        return (int) call_user_func(match ($type) {
                TypeDataEnum::TEXT => function () use ($token) {
                    $supposedToken = $this->findSupposedToken($token);

                    return (($supposedToken / self::DIMENSION) * TextDTO::COST) * AccountService::DIMENSION_TOKEN;
                },
                TypeDataEnum::IMAGE => function () use ($token) {

                    return ($token * ImageDTO::COST) * AccountService::DIMENSION_TOKEN;
                },
                default => throw new Exception('UnSupported type for cost'),
            });
    }

    public function findCost(TypeDataEnum $type, int $token): int
    {
        return (int) match ($type) {
                TypeDataEnum::TEXT => (($token / self::DIMENSION) * TextDTO::COST) * AccountService::DIMENSION_TOKEN,
                TypeDataEnum::IMAGE => ($token * ImageDTO::COST) * AccountService::DIMENSION_TOKEN,
                default => throw new Exception('UnSupported type for cost'),
            };
    }

    private function findSupposedToken(string $text): int
    {
        return ((int) (str_word_count($text) / 75) * 100) + self::ADDITIONAL_COUNT_TOKEN;
    }

    private function findMaxAvailableToken(string $text): int
    {
        $supposedToken = $this->findSupposedToken($text);

        if ($supposedToken >= ($this->maxToken / 2)) {
            throw new TooMuchTokenException('Supposed count of tokens: ' . $supposedToken);
        }

        return (int) ($this->maxToken - $supposedToken);
    }

    private function image(string $prompt, string $type, string $imagePath = ''): ImageInterface
    {
        try {
            $typeOperation = 'image';
            $data = [
                "prompt" => $prompt,
                "n" => $this->countImage,
                "size" => "512x512",
                "response_format" => $type,
            ];

            if ($imagePath) {
                $data['image'] = curl_file_create($imagePath);
                $typeOperation = 'imageEdit';
            }

            $complete = $this->openAI->{$typeOperation}($data);
            $this->logger->debug("Response image", [
                'response' => $complete,
                'prompt' => $prompt
            ]);

            $this->checkError($complete);

            return $this->serializer->deserialize($complete, ImageDTO::class, JsonEncoder::FORMAT, [
                    AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true
            ]);
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            throw $ex;
        }
    }

    private function completion(string $prompt): TextInterface
    {
        try {
            $data = [
                'model' => 'text-davinci-003',
                'prompt' => $prompt,
                'temperature' => 0.9,
                'frequency_penalty' => 0,
                'presence_penalty' => 0.6,
            ];

            $complete = $this->openAI->completion($data);
            $this->logger->debug("Response openIA completion", $data);

            $this->checkError($complete);

            return $this->serializer->deserialize($complete, TextDTO::class, JsonEncoder::FORMAT, [
                    AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true
            ]);
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            throw $ex;
        }
    }

    private function chat(string $prompt, string $system): TextInterface
    {
        try {
            $data = [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        "role" => "system",
                        "content" => $system
                    ],
                    [
                        "role" => "user",
                        "content" => $prompt
                    ]
                ],
                'temperature' => 1.0,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
            ];

            $complete = $this->openAI->chat($data);
            $this->logger->debug("Response openIA completion", $data);

            $this->checkError($complete);

            return $this->serializer->deserialize($complete, ChatDTO::class, JsonEncoder::FORMAT, [
                    AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true
            ]);
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            throw $ex;
        }
    }

    private function checkError(string $response): void
    {
        $responseInfo = $this->openAI->getCURLInfo();
        if (!empty($responseInfo['http_code']) && $responseInfo['http_code'] === Response::HTTP_OK) {
            return;
        }

        $errorMessage = json_decode($response, true);
        if (!empty($errorMessage['error']['type']) && $errorMessage['error']['type'] === self::RICH_LIMIT_ERROR_TYPE) {
            throw new RecoverableMessageHandlingException();
        }

        if (!empty($errorMessage['error']['type']) && $errorMessage['error']['type'] === self::INVALID_REQUEST) {
            throw new UnrecoverableMessageHandlingException($errorMessage['error']['message']);
        }

        throw new AIException('AI Wrong format respone: ' . $response);
    }
}
