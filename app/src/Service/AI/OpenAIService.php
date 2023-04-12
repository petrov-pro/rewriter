<?php
namespace App\Service\AI;

use Exception;
use InvalidArgumentException;
use Orhanerday\OpenAi\OpenAi;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class OpenAIService implements AIInterface
{

    public const TRANSPORT_NAME = 'rewrite';
    public const ADDITIONAL_COUNT_TOKEN = 200;
    private const CACHE_TIME = 604800;

    public function __construct(
        private int $maxToken,
        private LoggerInterface $logger,
        private OpenAi $openAI,
        private CacheInterface $cache
    )
    {
        
    }

    public function rewrite(string $textRewrite, string $translateTo = ''): string
    {
        return $this->cache->get($this->makeHash($textRewrite, $translateTo), function (ItemInterface $item) use ($textRewrite, $translateTo) {
                $item->expiresAfter(self::CACHE_TIME);

                return $this->completion('Rewrite, leave tags' . ($translateTo ? ' and translate to ' . $translateTo : '') . ': ' . $textRewrite);
            });
    }

    public function keywords(string $title, int $count = 3): string
    {
        return $this->cache->get($this->makeHash($title, $count), function (ItemInterface $item) use ($title, $count) {
                $item->expiresAfter(self::CACHE_TIME);

                return $this->completion("Make $count main keywords make in one string over a comma: " . $title);
            });
    }

    public function translate(string $text, string $lang): string
    {
        return $this->cache->get($this->makeHash($text, $lang), function (ItemInterface $item) use ($text, $lang) {
                $item->expiresAfter(self::CACHE_TIME);

                return $this->completion("Translate to $lang, leave tags: " . $text);
            });
    }

    public function createImage(string $prompt, string $type = 'url'): string
    {
        return $this->cache->get($this->makeHash($prompt, $type), function (ItemInterface $item) use ($prompt, $type) {
                $item->expiresAfter(self::CACHE_TIME);

                return $this->image($prompt, $type);
            });
    }

    public function editImage(string $prompt, string $imagePath, string $type = 'url'): string
    {
        return $this->cache->get($this->makeHash($prompt, $imagePath, $type), function (ItemInterface $item) use ($prompt, $imagePath, $type) {
                $item->expiresAfter(self::CACHE_TIME);

                return $this->image($prompt, $type, $imagePath);
            });
    }

    public function findCountToken(string $text): int
    {
        $supposedToken = ((str_word_count($text) / 75) * 100) + self::ADDITIONAL_COUNT_TOKEN;

        if ($supposedToken >= ($this->maxToken / 2)) {
            throw new TooMuchTokenException('Supposed count of tokens: ' . $supposedToken);
        }

        return (int) ($this->maxToken - $supposedToken);
    }

    private function image(string $prompt, string $type, string $imagePath = ''): string
    {
        try {
            $typeOperation = 'image';
            $data = [
                "prompt" => $prompt,
                "n" => 1,
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

            return $response->data[0]->url ?? throw new InvalidArgumentException('Seems wrong answer from open AI. Miss data[0]->url');
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            throw $ex;
        }
    }

    private function completion(string $prompt): string
    {
        try {
            $supposedToken = $this->findCountToken($prompt);
            $complete = $this->openAI->completion([
                'model' => 'text-davinci-003',
                'prompt' => $prompt,
                'temperature' => 0.9,
                'max_tokens' => $supposedToken,
                'frequency_penalty' => 0,
                'presence_penalty' => 0.6,
            ]);

            $response = json_decode($complete);
            $this->logger->debug("Response openIA completion", [
                'supposed_token' => $supposedToken,
                'response' => $response,
                'text_incoming' => $prompt
            ]);

            return $response->choices[0]->text ?? throw new InvalidArgumentException('Seems wrong answer from open AI. Miss choices[0]->text');
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            throw $ex;
        }
    }

    protected function makeHash(...$params): string
    {
        return md5(serialize($params));
    }
}
