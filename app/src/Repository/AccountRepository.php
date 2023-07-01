<?php
namespace App\Repository;

use App\Entity\Account;
use App\Exception\NotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Account>
 *
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(array $criteria, array $orderBy = null)
 * @method Account[]    findAll()
 * @method Account[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    public function save(Account $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Account $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findLockBy(int $customerId): Account
    {
        $account = $this->createQueryBuilder('a')
            ->where('a.customer = :customerId')
            ->setParameter('customerId', $customerId)
            ->getQuery()
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)
            ->setHint(Query::HINT_REFRESH, true)
            ->getOneOrNullResult();

        if (!$account) {
            throw new NotFoundException('Not found account for customer: ' . $customerId);
        }

        return $account;
    }

    public function startTransaction(): void
    {
        if (!$this->getEntityManager()->getConnection()->isTransactionActive()) {
            $this->getEntityManager()->beginTransaction();
        }
    }

    public function commitTransaction(): void
    {
        if ($this->getEntityManager()->getConnection()->isTransactionActive()) {
            $this->getEntityManager()->commit();
        }
    }

    public function rollbackTransaction(): void
    {
        if ($this->getEntityManager()->getConnection()->isTransactionActive()) {
            $this->getEntityManager()->rollback();
        }
    }
}
