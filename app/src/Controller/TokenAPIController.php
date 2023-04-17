<?php
namespace App\Controller;

use App\Entity\APIToken;
use App\Repository\UserRepository;
use App\Util\APIEnum;
use App\Util\Helper;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: ['', '/api/v1/token'])]
class TokenAPIController extends AbstractController
{

    public function __construct(
        private UserRepository $userRepository
    )
    {
        
    }

    #[Route(path: ['', '/'], methods: 'POST')]
    public function regenerate(Request $request): JsonResponse
    {
        $user = $this->userRepository->findByEmail($request->get('email'));
        foreach ($user->getAPITokens() as $apiToken) {
            $user->removeAPIToken($apiToken);
        }

        $term = (int) $request->get('term', 1);
        $user = $this->userRepository->addApiToken($user, $term);
        $this->userRepository->save($user, true);

        return $this->json($user, Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }

    #[Route(path: ['', '/'], methods: 'DELETE')]
    public function delete(Request $request): JsonResponse
    {
        $user = $this->userRepository->findByEmail($request->get('email'));
        foreach ($user->getAPITokens() as $apiToken) {
            $user->removeAPIToken($apiToken);
        }
        $this->userRepository->save($user, true);

        return $this->json($user, Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }
}
