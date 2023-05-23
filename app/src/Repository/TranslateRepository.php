<?php
namespace App\Repository;

use App\Entity\Translate;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
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

    public function countBy(int $siteId, DateTimeImmutable $startDt, DateTimeImmutable $endDt): ?int
    {

        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addScalarResult('id', 'id');
        return $this->getEntityManager()
                ->createNativeQuery("SELECT COUNT(count.context_id) as id FROM (SELECT context_id FROM translate WHERE  site_id = :siteId AND create_at BETWEEN :startDt AND :endDt GROUP BY context_id) as count;", $rsm)
                ->setParameter('startDt', $startDt->format('Y-m-d H:i:s'))
                ->setParameter('endDt', $endDt->format('Y-m-d H:i:s'))
                ->setParameter('siteId', $siteId)
                ->getSingleScalarResult();
    }
}
