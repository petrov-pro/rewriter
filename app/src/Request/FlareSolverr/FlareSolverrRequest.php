<?php
namespace App\Request\FlareSolverr;

use App\Request\FlareSolverr\DTO\SiteDTO;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FlareSolverrRequest
{

    public function __construct(
        private HttpClientInterface $flareSolverrHttpClient,
        private SerializerInterface $serializer,
        private int $maxTimeout,
        private string $urlProxy
    )
    {
        
    }

    public function get(string $url): SiteDTO
    {
        return $this->request([
                'cmd' => 'request.get',
                'url' => $url,
                'maxTimeout' => $this->maxTimeout
        ]);
    }

    private function request(array $data): SiteDTO
    {
        if ($this->urlProxy) {
            $data['proxy'] = [
                'url' => $this->urlProxy
            ];
        }

        $response = $this->flareSolverrHttpClient->request('POST', 'v1', [
            'json' => $data
        ]);
        $body = $response->getContent();

        if (!$body) {
            throw new ClientException($response);
        }

        return $this->serializer->deserialize($body, SiteDTO::class, JsonEncoder::FORMAT, [
                AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true
        ]);
    }
}
