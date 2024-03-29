<?php
namespace App\Controller;

use App\Controller\DTO\ErrorDTO;
use App\Exception\NotFoundException;
use App\Util\APIEnum;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\PessimisticLockException;
use InvalidArgumentException;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Validator\Exception\ValidatorException;

class ExceptionController
{

    public function show(FlattenException $exception): JsonResponse
    {
        $errorMessage = match ($exception->getClass()) {
            NotEncodableValueException::class => 'Empy body',
            ValidatorException::class => $exception->getMessage(),
            NotFoundException::class => $exception->getMessage(),
            NotNormalizableValueException::class => $exception->getMessage(),
            InvalidArgumentException::class => $exception->getMessage(),
            UniqueConstraintViolationException::class => 'Found duplicate',
            PessimisticLockException::class => 'Should repeat request later',
            default => $exception->getStatusText()
        };

        return new JsonResponse((new ErrorDTO(APIEnum::STATUS_ERROR->value, $errorMessage))->toArray(), $exception->getStatusCode());
    }
}
