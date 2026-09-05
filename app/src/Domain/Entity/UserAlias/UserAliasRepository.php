<?php

namespace App\Domain\Entity\UserAlias;

use App\Domain\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<UserAlias>
 */
class UserAliasRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, UserAlias::class);
    }

    public function findById(string $id): ?UserAlias
    {
        $user = $this->find($id);
        if ($user) {
            return $user;
        }
        return null;
    }

    public function findByAliasNameForUser(User $user, string $aliasName): ?UserAlias
    {
        $user = $this->findOneBy(['user' => $user, 'aliasName' => $aliasName]);
        if ($user) {
            return $user;
        }
        return null;
    }

    /**
     * Whoever currently holds this address, if anybody.
     *
     * Used by the importer to tell a new alias from one that has to move
     * between accounts. Takes the first match rather than asserting one: the
     * entity declares a uniq_alias constraint that no migration ever created,
     * so a database can legitimately hold duplicates today.
     */
    public function findOneByAliasName(string $aliasName): ?UserAlias
    {
        return $this->findOneBy(['aliasName' => $aliasName]);
    }

    /**
     * The addresses a user owns, for deciding whether they may act on a greylist
     * row. Returns the names rather than entities because ConnectVoter asks this
     * once per request and then answers from memory, however many rows a bulk
     * action carries.
     *
     * @return string[]
     */
    public function findAliasNamesForUserId(string $userId): array
    {
        return $this->createQueryBuilder('ua')
            ->select('ua.aliasName')
            ->where('ua.user = :user')
            ->setParameter('user', $userId)
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findFiltered(?User $user = null, ?string $query = null, ?string $start = null, string|int $max = 20, ?string $sortBy = null, bool $desc = false): iterable|Paginator
    {
        $qb = $this->createDefaultQueryBuilder($user);

        if ($query) {
            $qb = $qb->andWhere('u.username LIKE :query OR ua.aliasName LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        if ($sortBy !== null) {
            $mapping = [
                'username' => 'u.username',
                'aliasName' => 'ua.aliasName'
            ];
            $qb = $qb->orderBy($mapping[$sortBy], $desc ? 'DESC' : 'ASC');
        } else {
            $qb = $qb->orderBy('ua.aliasName', 'ASC');
        }

        if ($start !== null) {
            $qb = $qb->setMaxResults($max)
                ->setFirstResult(intval($start) === 0 ? $start : (($start) * $max));
            return new Paginator($qb, false);
        }
        return $qb->getQuery()
            ->getResult();
    }


    private function createDefaultQueryBuilder(?User $user = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('ua')
            ->leftJoin('ua.user', 'u');

        if ($user) {
            $qb = $qb->where('ua.user = :user')
                ->setParameter('user', $user);
        }
        return $qb;
    }

    public function save(UserAlias $user): UserAlias
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
        return $user;
    }

    public function createBatchSaver(int $batchSize = 10): callable
    {
        $count = 0;
        return function (UserAlias $user) use (&$count, $batchSize): bool {
            $count++;
            $this->save($user);
            if (($count % $batchSize) === 0) {
                $this->getEntityManager()->flush();
                $this->getEntityManager()->clear(); // Detaches all objects from Doctrine!
                return true;
            }
            return false;
        };
    }

    public function delete(UserAlias $user): void
    {
        $this->getEntityManager()->remove($user);
    }

}
