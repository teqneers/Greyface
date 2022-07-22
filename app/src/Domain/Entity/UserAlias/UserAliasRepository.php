<?php

namespace App\Domain\Entity\UserAlias;

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

    public function findAll($start = null, $max = 20): iterable|Paginator
    {
        $qb = $this->createDefaultQueryBuilder()
            ->orderBy('ua.aliasName', 'ASC');
        if ($start !== null) {
            $qb = $qb->setMaxResults($max)
                ->setFirstResult($start);
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
        return $user;
    }

    public function delete(UserAlias $user): void
    {
        $this->_em->remove($user);
    }

}