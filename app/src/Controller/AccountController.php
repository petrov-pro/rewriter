<?php
namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\AccountService;
use App\Util\APIEnum;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: ['', '/api/v1/account'])]
class AccountController extends AbstractController
{

    public function __construct(
        private Security $security,
        private AccountService $accountService,
        private UserRepository $userRepository
    )
    {
        
    }

    #[Route(path: ['', '/'], methods: 'GET')]
    public function get(): JsonResponse
    {
        return $this->json($this->security->getUser()->getAccount(), Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }

    #[Route(path: ['', '/billing'], methods: 'GET')]
    public function billing(): JsonResponse
    {
        return $this->json($this->security->getUser()->getBillings(), Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }

    #[Route(path: ['', '/'], methods: 'PUT')]
    public function update(Request $request): JsonResponse
    {
        $user = $this->userRepository->findByEmail($request->get('email'));
        $deposit = $request->get('deposit') ?? throw new InvalidArgumentException('Miss field deposit');
        $this->accountService->deposit($deposit, $user->getId(), true);

        return $this->json($user, Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }

    #[Route(path: ['', '/'], methods: 'DELETE')]
    public function delete(Request $request): JsonResponse
    {
        $user = $this->userRepository->findByEmail($request->get('email'));
        $this->accountService->setBalance(0, $user->getId(), true);

        return $this->json($user, Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }
}
