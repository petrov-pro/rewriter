<?php
namespace App\Service\Spread;

use App\Service\Spread\DTO\BaseDTO;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class BaseProvider
{

    protected const CACHE_TIME = 604800;

    public function __construct(
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
        protected SerializerInterface $serializer,
        protected ValidatorInterface $validator,
        protected CacheInterface $cache
    )
    {
        
    }

    protected function deserialize(array $data, String $type): BaseDTO
    {
        $entity = $this->serializer->deserialize(\json_encode($data), $type, JsonEncoder::FORMAT);
        $errors = $this->validator->validate($entity);

        if (count($errors) > 0) {
            throw new ValidatorException($errors[0]->getPropertyPath() . ' - ' . $errors[0]->getMessage());
        }

        return $entity;
    }

    protected function serialize(BaseDTO $entity): string
    {
        return $this->serializer->serialize($entity, JsonEncoder::FORMAT, [
                AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => true,
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                AbstractNormalizer::IGNORED_ATTRIBUTES => BaseDTO::toArray()
        ]);
    }

    protected function sendRequest(string $method, string $url, array $options): string
    {
        $response = $this->httpClient->request(
            $method,
            $url,
            $options
        );

        return $response->getContent();
    }
}
