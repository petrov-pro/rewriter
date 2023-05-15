<?php
namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Util\APIEnum;
use Nelmio\ApiDocBundle\Annotation\Areas;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: 'Token')]
#[OA\Response(
        response: Response::HTTP_OK,
        description: 'Successful response',
        content: new Model(type: User::class, groups: [APIEnum::GROUP_NAME_SHOW->value])
    )]
#[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Something was wrong',
        content: new OA\JsonContent(
            [new OA\Examples(example: 'Example error', summary: '', value: '{"status":"error","message":"Message of error"}')]
        )
    )
]
#[Route(path: ['', '/api/v1/token'])]
class TokenController extends AbstractController
{

    public function __construct(
        private UserRepository $userRepository
    )
    {
        
    }

    #[Areas(['admin'])]
    #[OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                new OA\Property(
                    property: 'email',
                    type: 'string'
                ),
                new OA\Property(
                    property: 'term',
                    type: 'int'
                )
                ]
            )
        )]
    #[IsGranted(User::ROLE_ADMIN)]
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

    #[Areas(['admin'])]
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
