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

    public function findAll($start = null, $max = 20): iterable|Paginator
    {
        $qb = $this->createDefaultQueryBuilder()
            ->orderBy('ua.aliasName', 'ASC');
        if ($start !== null) {
            $qb = $qb->setMaxResults($max)
                ->setFirstResult(intval($start) === 0 ? $start : (($start) * $max));
            return new Paginator($qb, false);
        }
        return $qb->getQuery()
            ->getResult();
    }


    private function createDefaultQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('ua');
    }

    public function save(UserAlias $user): UserAlias
    {
        $this->_em->persist($user);
        $this->_em->flush();
        return $user;
    }

    public function createBatchSaver(int $batchSize = 10): callable
    {
        $count = 0;
        return function (UserAlias $user) use (&$count, $batchSize): bool {
            $count++;
            $this->save($user);
            if (($count % $batchSize) === 0) {
                $this->_em->flush();
                $this->_em->clear(); // Detaches all objects from Doctrine!
                return true;
            }
            return false;
        };
    }

    public function delete(UserAlias $user): void
    {
        $this->_em->remove($user);
    }

}
