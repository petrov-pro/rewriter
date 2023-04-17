<?php
namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AccountService;
use App\Util\APIEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: ['', '/api/v1/user'])]
class UserAPIController extends AbstractController
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

    #[Route(path: ['', '/'], methods: 'GET')]
    public function get(): JsonResponse
    {
        return $this->json($this->security->getUser(), Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }

    #[Route(path: ['', '/'], methods: 'POST')]
    public function create(Request $request): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, JsonEncoder::FORMAT, [
            AbstractNormalizer::CALLBACKS => [
                'password' => function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
                    return empty($innerObject) ? '' : md5($innerObject);
                }
            ],
            'groups' => [APIEnum::GROUP_NAME_CREATE->value]
        ]);

        $this->validate($user, APIEnum::GROUP_NAME_CREATE->value);

        $term = (int) $request->get('term', 1);
        $user = $this->userRepository->addApiToken($user, $term);

        $this->userRepository->save($user, false);
        $user->setAccount($this->accountService->setBalance(0, $user->getId(), true));

        return $this->json($user, Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }

    #[Route(path: ['', '/'], methods: 'PUT')]
    public function update(Request $request): JsonResponse
    {
        $userRequest = $this->serializer->deserialize($request->getContent(), User::class, JsonEncoder::FORMAT, [
            'groups' => [APIEnum::GROUP_NAME_UPDATE->value]
        ]);

        $this->validate($userRequest, APIEnum::GROUP_NAME_UPDATE->value);

        $user = $this->security->getUser();

        $user->setLang($userRequest->getLang())
            ->setContextCategory($user->getContextCategory());

        $this->userRepository->save($user, true);

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
