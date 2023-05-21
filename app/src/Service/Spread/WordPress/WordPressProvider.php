<?php
namespace App\Service\Spread\WordPress;

use App\Entity\Context;
use App\Entity\Site;
use App\Service\Spread\BaseProvider;
use App\Service\Spread\SpreadProviderInterface;
use App\Service\Spread\WordPress\DTO\PostCreateDTO;
use App\Util\Helper;
use Exception;
use Nette\Utils\Image;
use Nette\Utils\Strings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\Cache\ItemInterface;

class WordPressProvider extends BaseProvider implements SpreadProviderInterface
{

    public const TYPE = 'wordpress';

    public function isSupport(string $providerType): bool
    {
        return $providerType == self::TYPE;
    }

    public function spread(Context $context, Site $site): void
    {

        $content = $this->deserialize($site->getSetting(), PostCreateDTO::class);
        $translate = $context->getTranslates()[0];

        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($translate->getTitle());
        $content->setTitle($translate->getTitle())
            ->setExcerpt($translate->getDescription())
            ->setSlug($slug)
            ->setContent($translate->getText());

        if (!$context->getImages()->isEmpty()) {
            $uploadIdImage = $this->uploadImage($content, $context->getImages(), $slug);

            if ($uploadIdImage) {
                $content->setFeaturedMedia($uploadIdImage);
            }
        }

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'auth_basic' => [$content->getLogin(), $content->getPassword()],
            'body' => $this->serialize($content)
        ];

        $response = \json_decode((string) $this->sendRequest(Request::METHOD_POST, $content->getApiUrl() . 'wp-json/wp/v2/posts', $options), true);
        if (empty($response['title'])) {
            throw new Exception('Empty answer from: ' . $content->getApiUrl());
        }

        $this->logger->info('Content was send to wordpress: ' . $content->getApiUrl());
    }

    private function uploadImage(PostCreateDTO $content, iterable $images, string $slug): string
    {
        foreach ($images as $image) {
            $uploadIdImage = $this->cache->get(Helper::generateHash(__METHOD__, [$image->getSite(), $image->getData()]),
                function (ItemInterface $item) use ($content, $image, $slug) {
                    $item->expiresAfter(self::CACHE_TIME);

                    foreach ($image->getData() as $key => $url) {
                        $imageData = file_get_contents($url);
                        if ($imageData === false) {
                            $this->logger->warning('Can not get media by url: ' . $url);
                            continue;
                        }

                        $typeImage = Image::detectTypeFromString($imageData);
                        $options = [
                            'headers' => [
                                'Content-Disposition' => 'attachment; filename="' . Strings::truncate($slug, 50) . '_' . $key . '.' . Image::typeToExtension($typeImage) . '"',
                                'Content-Type' => Image::typeToMimeType($typeImage),
                            ],
                            'auth_basic' => [$content->getLogin(), $content->getPassword()],
                            'body' => $imageData
                        ];

                        $response = \json_decode((string) $this->sendRequest(Request::METHOD_POST, $content->getApiUrl() . 'wp-json/wp/v2/media', $options), true);
                        if (empty($response['id'])) {
                            $this->logger->warning('Can not upload media for site: ' . $content->getApiUrl());
                        } else {
                            $uploadIdImage = $response['id'];
                        }
                    }

                    return $uploadIdImage ?? '';
                });
        }

        return $uploadIdImage ?? '';
    }
}
