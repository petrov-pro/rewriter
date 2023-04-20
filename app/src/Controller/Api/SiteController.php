<?php
namespace App\Controller\Api;

use App\Entity\Site;
use App\Entity\User;
use App\Repository\SiteRepository;
use App\Util\APIEnum;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: ['', '/api/v1/site'])]
class SiteController extends AbstractController
{

    public function __construct(
        private Security $security,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
        private SiteRepository $siteRepository
    )
    {
        
    }

    #[Route(path: ['', '/'], methods: 'GET')]
    public function get(): JsonResponse
    {
        return $this->json($this->security->getUser()->getSites(), Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }

    #[Route(path: ['', '/'], methods: 'POST')]
    public function create(Request $request): JsonResponse
    {
        /** @var Site $site */
        $site = $this->serializer->deserialize($request->getContent(), Site::class, JsonEncoder::FORMAT, [
            AbstractObjectNormalizer::GROUPS => [APIEnum::GROUP_NAME_CREATE->value]
        ]);

        $this->validate($site, APIEnum::GROUP_NAME_CREATE->value);
        $site->setCustomer($this->security->getUser());

        $this->siteRepository->save($site, true);

        return $this->json($site, Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }

    #[Route(path: ['', '/{id}'], methods: 'PUT')]
    public function update(Site $site, Request $request): JsonResponse
    {
        $this->isUserEntity($site);
        /** @var Site $site */
        $site = $this->serializer->deserialize($request->getContent(), Site::class, JsonEncoder::FORMAT, [
            AbstractObjectNormalizer::OBJECT_TO_POPULATE => $site,
            AbstractObjectNormalizer::GROUPS => [APIEnum::GROUP_NAME_UPDATE->value]
        ]);

        $this->validate($site, APIEnum::GROUP_NAME_UPDATE->value);
        $this->siteRepository->save($site, true);

        return $this->json($site, Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }

    #[Route(path: ['', '/{id}'], methods: 'DELETE')]
    public function delete(Site $site, Request $request): JsonResponse
    {
        $this->isUserEntity($site);
        $this->siteRepository->remove($site, true);

        return $this->json($site, Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }

    private function isUserEntity(Site $entity): void
    {
        /** @var User $user */
        $user = $this->security->getUser();
        if ($user->getId() !== $entity->getCustomer()->getId()) {
            throw new InvalidArgumentException('User invalid');
        }
    }

    private function validate(object $entity, string $group)
    {
        $errors = $this->validator->validate($entity, null, ['Default', $group]);

        if (count($errors) > 0) {
            throw new ValidatorException($errors[0]->getPropertyPath() . ' - ' . $errors[0]->getMessage());
        }
    }
}
