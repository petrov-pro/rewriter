<?php
namespace App\Repository;

use App\Entity\Translate;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Translate>
 *
 * @method Translate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Translate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Translate[]    findAll()
 * @method Translate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TranslateRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Translate::class);
    }

    public function save(Translate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Translate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function countBy(int $contextId, int $siteId, DateTimeImmutable $startDt, DateTimeImmutable $endDt): ?int
    {
        return $this->createQueryBuilder('t')
                ->select('count(t.id)')
                ->andWhere('t.context = :contextId')
                ->andWhere('t.site = :siteId')
                ->andWhere('t.create_at BETWEEN :startDt AND :endDt')
                ->setParameter('contextId', $contextId)
                ->setParameter('siteId', $siteId)
                ->setParameter('startDt', $startDt->format('Y-m-d H:i:s'))
                ->setParameter('endDt', $startDt->format('Y-m-d H:i:s'))
                ->getQuery()
                ->getSingleScalarResult();
    }
}
