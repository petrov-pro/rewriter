<?php
namespace App\Controller;

use App\Controller\DTO\ErrorDTO;
use App\Util\APIEnum;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Exception\ValidatorException;
use function dd;

class ExceptionController
{

    public function show(FlattenException $exception): JsonResponse
    {
        $errorMessage = match ($exception->getClass()) {
            NotEncodableValueException::class => 'Empy body',
            ValidatorException::class => $exception->getMessage(),
            default => $exception->getStatusText()
        };

        return new JsonResponse((new ErrorDTO(APIEnum::STATUS_ERROR->value, $errorMessage))->toArray(), $exception->getStatusCode());
    }
}
