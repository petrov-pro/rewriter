<?php
namespace App\Controller;

use App\Util\APIEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: ['', '/api/v1/user'])]
class UserAPIController extends AbstractController
{

    public function __construct(private Security $security)
    {
        
    }

    #[Route(path: ['', '/'], methods: 'GET')]
    public function get(): JsonResponse
    {
        return $this->json($this->security->getUser(), 200, [], [
                'groups' => [APIEnum::GROUP_NAME->value]
        ]);
    }

    #[Route(path: ['', '/'], methods: 'POST')]
    public function create(): JsonResponse
    {
        return $this->json($this->security->getUser(), 200, [], [
                'groups' => [APIEnum::GROUP_NAME->value]
        ]);
    }
}
