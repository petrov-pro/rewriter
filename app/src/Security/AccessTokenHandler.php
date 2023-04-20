<?php
namespace App\Security;

use App\Repository\APITokenRepository;
use DateTime;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{

    public function __construct(
        private APITokenRepository $repository
    )
    {
        
    }

    public function getUserBadgeFrom(string $tokenAccess): UserBadge
    {
        // e.g. query the "access token" database to search for this token
        $tokenEntity = $this->repository->findOneByToken($tokenAccess);
        if (null === $tokenEntity || !$tokenEntity->isValid()) {
            throw new AccessDeniedException('Invalid credentials.');
        }

        if ($tokenEntity->getDate() <= new DateTime()) {
            throw new AccessDeniedException('Token time expired.');
        }

        $user = $tokenEntity->getCustomer();
        if (!$user) {
            throw new AccessDeniedException('User not found');
        }

        // and return a UserBadge object containing the user identifier from the found token
        return new UserBadge($user->getEmail());
    }
}
