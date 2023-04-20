<?php
namespace App\Security;

use App\Controller\DTO\ErrorDTO;
use App\Util\APIEnum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class AccessExceptionHandler implements AuthenticationFailureHandlerInterface, AuthenticationEntryPointInterface, AccessDeniedHandlerInterface
{

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {

        return new Response(new ErrorDTO(APIEnum::STATUS_ERROR->value, $accessDeniedException->getMessage()), 403);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {

        return new Response(new ErrorDTO(APIEnum::STATUS_ERROR->value, $$exception->getMessage()), 403);
    }

    public function start(Request $request, AuthenticationException $exception = null): Response
    {
        return new Response(new ErrorDTO(APIEnum::STATUS_ERROR->value, $exception->getPrevious()->getMessage() ?? $exception->getMessage()), 401);
    }
}
