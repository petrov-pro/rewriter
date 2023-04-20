<?php
namespace App\Controller\Api;

use App\Repository\ContextRepository;
use App\Util\APIEnum;
use App\Util\Helper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route(path: ['', '/api/v1/context'])]
class ContextController extends AbstractController
{

    public function __construct(private TagAwareCacheInterface $cache, private Security $security)
    {
        
    }

    #[Route(path: ['', '/'], methods: 'GET')]
    public function get(Request $request, ContextRepository $repository): JsonResponse
    {
        $page = $request->query->get('page') ?? 0;
        $limit = $request->query->get('limit') ?? 10;
        $source = $request->query->get('source') ?? '';

        $contexts = $this->cache->get(Helper::generateHash(__CLASS__, $request->query->all()), function (ItemInterface $item) use ($repository, $page, $limit, $source) {
            $item->expiresAfter((int) APIEnum::CACHE_LIVE->value);
            $item->tag([APIEnum::CACHE_TAG->value, APIEnum::CACHE_TAG_USER->value . $this->security->getUser()->getId()]);

            return $repository->findPublicContext($this->security->getUser()->getId(), $page, $limit, $source);
        });

        return $this->json($contexts, Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }
}
