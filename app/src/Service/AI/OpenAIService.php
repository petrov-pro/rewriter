<?php
namespace App\Service\AI;

use App\Service\AI\DTO\ImageInterface;
use App\Service\AI\DTO\OpenAI\ImageDTO;
use App\Service\AI\DTO\OpenAI\TextDTO;
use App\Service\AI\DTO\TextInterface;
use App\Util\TypeDataEnum;
use Exception;
use Orhanerday\OpenAi\OpenAi;
use Psr\Log\LoggerInterface;
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

    public function rewrite(mixed $idt, string $textRewrite, string $translateTo = ''): TextInterface
    {
        return $this->cache->get($this->makeHash($idt, $textRewrite, $translateTo), function (ItemInterface $item) use ($textRewrite, $translateTo) {
                $item->expiresAfter(self::CACHE_TIME);

                return $this->completion('Rewrite, leave tags' . ($translateTo ? ' and translate to ' . $translateTo : '') . ': ' . $textRewrite);
            });
    }

    public function keywords(mixed $idt, string $title, int $count = 3): TextInterface
    {
        return $this->cache->get($this->makeHash($idt, $title, $count), function (ItemInterface $item) use ($title, $count) {
                $item->expiresAfter(self::CACHE_TIME);

                return $this->completion("Make $count main keywords make in one string over a comma: " . $title);
            });
    }

    public function translate(mixed $idt, string $text, string $lang): TextInterface
    {
        return $this->cache->get($this->makeHash($idt, $text, $lang), function (ItemInterface $item) use ($text, $lang) {
                $item->expiresAfter(self::CACHE_TIME);

                return $this->completion("Translate to $lang, leave tags: " . $text);
            });
    }

    public function createImage(mixed $idt, string $prompt, string $type = 'url'): ImageInterface
    {
        return $this->cache->get($this->makeHash($idt, $prompt, $type), function (ItemInterface $item) use ($prompt, $type) {
                $item->expiresAfter(self::CACHE_TIME);

                return $this->image($prompt, $type);
            });
    }

    public function editImage(mixed $idt, string $prompt, string $imagePath, string $type = 'url'): ImageInterface
    {
        return $this->cache->get($this->makeHash($idt, $prompt, $imagePath, $type), function (ItemInterface $item) use ($prompt, $imagePath, $type) {
                $item->expiresAfter(self::CACHE_TIME);

                return $this->image($prompt, $type, $imagePath);
            });
    }

    public function findSupposedCost(TypeDataEnum $type, mixed $token): int
    {
        return (int) call_user_func(match ($type) {
                TypeDataEnum::TEXT => function () use ($token) {
                    $supposedToken = $this->findSupposedToken($token);

                    return ((int) ($supposedToken / self::DIMENSION)) * TextDTO::COST;
                },
                TypeDataEnum::IMAGE => function () use ($token) {

                    return ((int) $token) * ImageDTO::COST;
                },
                default => throw new Exception('UnSupported type for cost'),
            });
    }

    public function findCost(TypeDataEnum $type, int $token): int
    {
        return (int) match ($type) {
                TypeDataEnum::TEXT => ($token / self::DIMENSION) * TextDTO::COST,
                TypeDataEnum::IMAGE => $token * ImageDTO::COST,
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
            $response = json_decode($complete);
            $this->logger->debug("Response image", [
                'response' => $response,
                'prompt' => $prompt
            ]);

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
            $supposedToken = $this->findMaxAvailableToken($prompt);
            $complete = $this->openAI->completion([
                'model' => 'text-davinci-003',
                'prompt' => $prompt,
                'temperature' => 0.9,
                'max_tokens' => $supposedToken,
                'frequency_penalty' => 0,
                'presence_penalty' => 0.6,
            ]);

            $this->logger->debug("Response openIA completion", [
                'supposed_token' => $supposedToken,
                'response' => $complete,
                'text_incoming' => $prompt
            ]);

            return $this->serializer->deserialize($complete, TextDTO::class, JsonEncoder::FORMAT, [
                    AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true
            ]);
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            throw $ex;
        }
    }

    private function makeHash(...$params): string
    {
        return md5(serialize($params));
    }
}
