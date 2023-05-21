<?php
namespace App\Service\Spread\WordPressCom;

use App\Entity\Context;
use App\Entity\Site;
use App\Service\Spread\BaseProvider;
use App\Service\Spread\SpreadProviderInterface;
use App\Service\Spread\WordPressCom\DTO\PostCreateDTO;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\AsciiSlugger;

class WordPressComProvider extends BaseProvider implements SpreadProviderInterface
{

    public const TYPE = 'wordpresscom';

    public function isSupport(string $providerType): bool
    {
        return $providerType == self::TYPE;
    }

    public function spread(Context $context, Site $site): void
    {
        $content = $this->deserialize($site->getSetting(), PostCreateDTO::class);

        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($context->getTitle());
        $translate = $context->getTranslates()[0];

        $content->setTitle($translate->getTitle())
            ->setExcerpt($translate->getDescription())
            ->setContent($translate->getText())
            ->setSlug($slug);

        if ($this->isCreatePost($content)) {
            $this->logger->info('Content duplicate to wordpress.com: ' . $content->getApiUrl());

            return;
        }

        $this->createPost($content);
        $this->logger->info('Content was send to wordpress.com: ' . $content->getApiUrl());
    }

    private function isCreatePost(PostCreateDTO $content): bool
    {
        $options = $this->baseHeader($content->getToken());
        $response = \json_decode($this->sendRequest(Request::METHOD_GET, $content->getApiUrl() . "sites/{$content->getSite()}/posts/slug:{$content->getSlug()}", $options), true);

        return !empty($response['title']);
    }

    private function createPost(PostCreateDTO $content): void
    {
        $options = $this->baseHeader($content->getToken());
        $options['body'] = $this->serialize($content);

        $response = \json_decode($this->sendRequest(Request::METHOD_POST, $content->getApiUrl() . "sites/{$content->getSite()}/posts/new", $options), true);
        if (empty($response['title'])) {
            throw new Exception('Empty answer from: ' . $content->getApiUrl());
        }
    }

    private function baseHeader(string $token): array
    {
        return [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'auth_bearer' => $token
        ];
    }
}
