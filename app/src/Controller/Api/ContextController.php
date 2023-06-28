<?php
namespace App\Controller\Api;

use App\Entity\Context;
use App\Repository\ContextRepository;
use App\Util\APIEnum;
use App\Util\Helper;
use Nelmio\ApiDocBundle\Annotation\Areas;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

#[OA\Tag(name: 'Content')]
#[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Something was wrong',
        content: new OA\JsonContent(
            [new OA\Examples(example: 'Example error', summary: '', value: '{"status":"error","message":"Message of error"}')]
        )
    )
]
#[Route(path: ['', '/api/v1/context'])]
class ContextController extends AbstractController
{

    public function __construct(private TagAwareCacheInterface $cache, private Security $security)
    {
        
    }

    #[Areas(['api'])]
    #[OA\Response(
            response: Response::HTTP_OK,
            description: 'Successful response',
            content: new Model(type: Context::class, groups: [APIEnum::GROUP_NAME_SHOW->value])
        )]
    #[OA\QueryParameter(
            name: 'page',
            description: 'Page content'
        )]
    #[OA\QueryParameter(
            name: 'limit',
            description: 'Limit content on the page'
        )]
    #[OA\QueryParameter(
            name: 'source',
            description: 'Spurce of content'
        )]
    #[OA\QueryParameter(
            name: 'site_id',
            description: 'Site id'
        )]
    #[Route(path: ['', '/'], methods: 'GET')]
    public function get(Request $request, ContextRepository $repository): JsonResponse
    {
        $page = $request->query->get('page') ?? 0;
        $limit = $request->query->get('limit') ?? 10;
        $source = $request->query->get('source') ?? '';
        $siteId = $request->query->get('site_id') ?? 0;
        $userId = $this->security->getUser()->getId();

        $contexts = $this->cache->get(Helper::generateHash(__METHOD__, array_merge($request->query->all(), ['user_id' => $userId])),
            function (ItemInterface $item) use ($repository, $page, $limit, $source, $siteId, $userId) {
                $item->expiresAfter((int) APIEnum::CACHE_LIVE->value);
                $item->tag([APIEnum::CACHE_TAG->value, APIEnum::CACHE_TAG_USER->value . $this->security->getUser()->getId()]);

                return $repository->findPublicContext($userId, $page, $limit, $source, $siteId);
            });

        return $this->json($contexts, Response::HTTP_OK, [], [
                'groups' => [APIEnum::GROUP_NAME_SHOW->value]
        ]);
    }
}
