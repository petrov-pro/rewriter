<?php
namespace App\Controller;

use App\Repository\ContextRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[Route(path: ['', '/api/v1'])]
class APIController extends AbstractController
{

    private const CACHE_LIVE = 3600;
    public const CACHE_TAG = 'api-external';

    public function __construct(private TagAwareCacheInterface $cache)
    {
        
    }

    #[Route(path: ['', '/context'], methods: 'GET')]
    public function content(Request $request, ContextRepository $repository): JsonResponse
    {
        $page = $request->query->get('page') ?? 0;
        $limit = $request->query->get('limit') ?? 10;
        $source = $request->query->get('source') ?? '';

        $contexts = $this->cache->get('api-' . md5(serialize($request->query->all())), function (ItemInterface $item) use ($repository, $page, $limit, $source) {
            $item->expiresAfter(self::CACHE_LIVE);
            $item->tag([self::CACHE_TAG, 'context']);

            return $repository->findPublicContext($page, $limit, $source);
        });

        return $this->json($contexts, 200, [], [AbstractNormalizer::IGNORED_ATTRIBUTES => [
                    'context',
                    'hash',
                    'status',
                    'id'
        ]]);
    }
}
