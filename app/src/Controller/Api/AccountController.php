<?php
namespace App\Controller\Api;

use App\Entity\Account;
use App\Entity\Billing;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AccountService;
use App\Util\APIEnum;
use InvalidArgumentException;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Nelmio\ApiDocBundle\Annotation\Areas;

#[OA\Tag(name: 'Account')]
#[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Something was wrong',
        content: new OA\JsonContent(
            [new OA\Examples(example: 'Example error', summary: '', value: '{"status":"error","message":"Message of error"}')]
        )
    )
]
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

    #[Areas(['api'])]
    #[OA\Response(
            response: Response::HTTP_OK,
            description: 'Successful response',
            content: new Model(type: Account::class, groups: [APIEnum::GROUP_NAME_SHOW->value])
        )]
    #[Route(path: ['', '/'], methods: Request::METHOD_GET)]
    public function get(): JsonResponse
    {
        return $this->json($this->security->getUser()->getAccount(), Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }

    #[Areas(['api'])]
    #[OA\Response(
            response: Response::HTTP_OK,
            description: 'Successful response',
            content: new Model(type: Billing::class, groups: [APIEnum::GROUP_NAME_SHOW->value])
        )]
    #[Route(path: ['', '/billing'], methods: Request::METHOD_GET)]
    public function billing(): JsonResponse
    {
        return $this->json($this->security->getUser()->getBillings(), Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }

    #[Areas(['manager'])]
    #[OA\Response(
            response: Response::HTTP_OK,
            description: 'Successful response',
            content: new Model(type: User::class, groups: [APIEnum::GROUP_NAME_SHOW->value])
        )]
    #[OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                new OA\Property(
                    property: 'email',
                    type: 'string'
                ),
                new OA\Property(
                    property: 'deposit',
                    type: 'int'
                )
                ]
            )
        )]
    #[IsGranted(User::ROLE_ADMIN)]
    #[Route(path: ['', '/'], methods: Request::METHOD_PUT)]
    public function update(Request $request): JsonResponse
    {
        $user = $this->userRepository->findByEmail($request->get('email'));
        $deposit = $request->get('deposit') ?? throw new InvalidArgumentException('Miss field deposit');
        $this->accountService->deposit($deposit, $user->getId(), true);

        return $this->json($user, Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }

    #[Areas(['manager'])]
    #[OA\Response(
            response: Response::HTTP_OK,
            description: 'Successful response',
            content: new Model(type: User::class, groups: [APIEnum::GROUP_NAME_SHOW->value])
        )]
    #[OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                new OA\Property(
                    property: 'email',
                    type: 'string'
                )
                ]
            )
        )]
    #[IsGranted(User::ROLE_ADMIN)]
    #[Route(path: ['', '/'], methods: Request::METHOD_DELETE)]
    public function delete(Request $request): JsonResponse
    {
        $user = $this->userRepository->findByEmail($request->get('email'));
        $this->accountService->setBalance(0, $user->getId(), true);

        return $this->json($user, Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }
}
