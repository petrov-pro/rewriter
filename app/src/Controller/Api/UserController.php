<?php
namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AccountService;
use App\Util\APIEnum;
use Nelmio\ApiDocBundle\Annotation\Areas;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag(name: 'User')]
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
#[Route(path: ['', '/api/v1/user'])]
class UserController extends AbstractController
{

    public function __construct(
        private Security $security,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
        private UserRepository $userRepository,
        private AccountService $accountService,
    )
    {
        
    }

    #[Areas(['user'])]
    #[Route(path: ['', '/'], methods: 'GET')]
    public function get(): JsonResponse
    {
        return $this->json($this->security->getUser(), Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }

    #[Areas(['admin'])]
    #[OA\RequestBody(
            required: true,
            content: new Model(type: User::class, groups: [APIEnum::GROUP_NAME_CREATE->value])
        )]
    #[IsGranted(User::ROLE_ADMIN)]
    #[Route(path: ['', '/'], methods: 'POST')]
    public function create(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->serializer->deserialize($request->getContent(), User::class, JsonEncoder::FORMAT, [
            AbstractNormalizer::CALLBACKS => [
                'password' => function ($innerObject) {
                    return empty($innerObject) ? '' : \md5($innerObject);
                }
            ],
            AbstractObjectNormalizer::GROUPS => [APIEnum::GROUP_NAME_CREATE->value]
        ]);

        $this->validate($user, APIEnum::GROUP_NAME_CREATE->value);

        $term = (int) $request->get('term', 1);
        $user->setRoles($user->getRoles())
            ->addQuickAPIToken($term);

        $this->userRepository->save($user, false);
        $user->setAccount($this->accountService->setBalance(0, $user->getId(), true));

        return $this->json($user, Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }

    #[Areas(['user'])]
    #[OA\RequestBody(
            required: true,
            content: new Model(type: User::class, groups: [APIEnum::GROUP_NAME_UPDATE->value])
        )]
    #[Route(path: ['', '/'], methods: 'PUT')]
    public function update(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->security->getUser();
        /** @var User $userUpdate */
        $userUpdate = $this->serializer->deserialize($request->getContent(), User::class, JsonEncoder::FORMAT, [
            AbstractObjectNormalizer::OBJECT_TO_POPULATE => $user,
            AbstractObjectNormalizer::GROUPS => [APIEnum::GROUP_NAME_UPDATE->value]
        ]);

        $this->validate($userUpdate, APIEnum::GROUP_NAME_UPDATE->value);
        $this->userRepository->save($userUpdate, true);

        return $this->json($user, Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }

    private function validate(object $entity, string $group)
    {
        $errors = $this->validator->validate($entity, null, ['Default', $group]);

        if (count($errors) > 0) {
            throw new ValidatorException($errors[0]->getPropertyPath() . ' - ' . $errors[0]->getMessage());
        }
    }
}
