<?php
namespace App\Service\Spread\WordPress;

use App\Entity\Context;
use App\Service\Spread\BaseProvider;
use App\Service\Spread\SpreadProviderInterface;
use App\Service\Spread\WordPress\DTO\PostCreateDTO;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class WordPressProvider extends BaseProvider implements SpreadProviderInterface
{

    public const TYPE = 'wordpress';

    public function isSupport(string $providerType): bool
    {
        return $providerType == self::TYPE;
    }

    public function spread(array $params, Context $context): void
    {
        if (!empty($params['post_create'])) {
            $content = $this->deserialize($params, PostCreateDTO::class);
        } else {
            $content = new PostCreateDTO();
        }

        $content->setTitle($context->getTitle())
            ->setExcerpt($context->getDescription())
            ->setContent($context->getText());

        $options = [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'auth_basic' => [$content->getLogin(), $content->getPassword()],
            'body' => $this->serialize($content)
        ];

        $response = \json_decode($this->sendRequest(Request::METHOD_POST, $content->getApiUrl() . 'wp-json/wp/v2/posts', $options), true);
        if (empty($response['title'])) {
            throw new Exception('Empty answer from: ' . $content->getApiUrl());
        }

        $this->logger->info('Content was send to wordpress: ' . $content->getApiUrl());
    }
}
