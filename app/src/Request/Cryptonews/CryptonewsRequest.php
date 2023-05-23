<?php
namespace App\Request\Cryptonews;

use App\Request\Cryptonews\DTO\NewsDTO;
use App\Util\CategoryMainEnum;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\UnwrappingDenormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CryptonewsRequest
{

    public function __construct(
        private HttpClientInterface $cryptonewsHttpClient,
        private SerializerInterface $serializer,
        private string $newsAPIKey,
        private string $newsItemCount
    )
    {
        
    }

    /**
     * @return NewsDTO[]
     */
    public function getNews(string $tickers): array
    {
        $url = 'api/v1?tickers=' . $tickers . '&page=1';
        return $this->request($url);
    }

    /**
     * @return NewsDTO[]
     */
    public function getGeneralNews(): array
    {
        $url = 'api/v1/category?section=general&page=1';
        return $this->request($url);
    }

    private function request($url): array
    {
        $response = $this->cryptonewsHttpClient->request('GET', $url . '&token=' . $this->newsAPIKey . '&items=' . $this->newsItemCount);
        $body = $response->getContent();

        if (!$body) {
            throw new ClientException($response);
        }

        return $this->serializer->deserialize($body, NewsDTO::class . '[]', JsonEncoder::FORMAT, [
                AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true,
                AbstractObjectNormalizer::SKIP_NULL_VALUES,
                AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES,
                AbstractNormalizer::CALLBACKS => [
                    'topics' => function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
                        return array_merge($innerObject, [CategoryMainEnum::CRYPTO->value]);
                    }
                ],
                UnwrappingDenormalizer::UNWRAP_PATH => '[data]'
        ]);
    }
}
