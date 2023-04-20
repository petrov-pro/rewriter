<?php
namespace App\Repository;

use App\Entity\APIToken;
use App\Entity\User;
use App\Service\AccountService;
use App\Util\Helper;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }

    public function findAllActive(array $categories): array
    {
        $query = $this->createQueryBuilder('u')
            ->innerJoin('u.apiTokens', 't', Join::WITH, "t.is_valid = true AND t.date >= CURRENT_TIMESTAMP()")
            ->innerJoin('u.account', 'a', Join::WITH, "a.balance > " . AccountService::MIN_BALANCE)
            ->innerJoin('u.site', 's', Join::WITH, 's.is_valid = true')
            ->orderBy('u.id', 'DESC');

        foreach ($categories as $key => $category) {
            $query->orWhere($query->expr()->like('u.context_category', ":category$key"));
            $query->setParameter(":category$key", '%' . $category . '%');
        }

        return $query->getQuery()
                ->execute();
    }

    public function findOrThrow(int $customerId): User
    {
        $user = $this->find($customerId);
        if (!$user) {
            throw new \Exception('Can not find user: ' . $customerId);
        }

        return $user;
    }

    public function findByEmail(string $email): User
    {
        $user = $this->findOneBy(['email' => $email]);

        if (!$user) {
            throw new \Exception('Can not find user: ' . $email);
        }

        return $user;
    }
}
