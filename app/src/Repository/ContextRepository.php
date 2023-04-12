<?php
namespace App\Repository;

use App\Entity\Context;
use App\Service\ContextService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Context>
 *
 * @method Context|null find($id, $lockMode = null, $lockVersion = null)
 * @method Context|null findOneBy(array $criteria, array $orderBy = null)
 * @method Context[]    findAll()
 * @method Context[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContextRepository extends ServiceEntityRepository
{

    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct($registry, Context::class);
    }

    public function findOneByHash(string $hash): ?Context
    {
        return $this->createQueryBuilder('r')
                ->andWhere('r.hash = :val')
                ->setParameter('val', $hash)
                ->getQuery()
                ->getOneOrNullResult();
    }

    public function findPublicContext(int $page, int $limit, string $source = ''): array
    {
        $query = $this->createQueryBuilder('c')
            ->select('c', 't')
            ->innerJoin('c.translates', 't', Join::WITH, "t.type='modify'")
            ->setFirstResult($page)
            ->setMaxResults($limit)
            ->where("c.status = '" . ContextService::STATUS_FINISH . "'")
            ->orderBy('c.id', 'DESC');

        if ($source) {
            $query->where('c.source_name = :source_name')
                ->setParameter('source_name', $source);
        }

        return $query->getQuery()
                ->execute();
    }
}
