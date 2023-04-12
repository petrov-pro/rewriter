<?php
namespace App\Security;

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

        return new Response(\json_encode([
                'message' => $accessDeniedException->getMessage(),
                'http_code' => 403
            ]), 403);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {

        return new Response(\json_encode([
                'message' => $exception->getMessage(),
                'http_code' => 403
            ]), 403);
    }

    public function start(Request $request, AuthenticationException $exception = null): Response
    {
        return new Response(\json_encode([
                'message' => $exception->getPrevious()->getMessage() ?? $exception->getMessage(),
                'http_code' => 401
            ]), 401);
    }
}
