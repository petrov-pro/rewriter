<?php
namespace App\Controller;

use App\Controller\DTO\ErrorDTO;
use App\Util\APIEnum;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class ExceptionController
{

    public function show(FlattenException $exception): JsonResponse
    {
        if ($exception->getClass() === NotEncodableValueException::class) {
            $message = 'Empy body';
        } else {
            $message = $exception->getStatusText();
        }

        return new JsonResponse((new ErrorDTO(APIEnum::STATUS_ERROR->value, $message))->toArray(), $exception->getStatusCode());
    }
}
