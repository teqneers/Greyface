<?php

namespace App\Domain\Entity\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @template-extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, User::class);
    }

    public function findById(string $id, bool $allowDeleted = false): ?User
    {
        $user = $this->find($id);
        if ($user && ($allowDeleted || !$user->isDeleted())) {
            return $user;
        }
        return null;
    }

    public function findByUsername(string $username, bool $allowDeleted = false): ?User
    {
        $user = $this->findOneBy(['username' => $username]);
        if ($user && ($allowDeleted || !$user->isDeleted())) {
            return $user;
        }
        return null;
    }


    public function findAll(bool $allowDeleted = false, string $query = null, string $start = null, string|int $max = 20, string $sortBy = null, bool $desc = false): iterable|Paginator
    {
        $qb = $this->createDefaultQueryBuilder($allowDeleted);

        if ($query) {
            $qb = $qb->andWhere('u.email LIKE :query OR u.username LIKE :query')
                ->setParameter('query', '%'.$query.'%');
        }

        if ($sortBy !== null) {
            $mapping = [
                'username' => 'u.username',
                'email' => 'u.email',
                'role' => 'u.role'
            ];
            $qb = $qb->orderBy($mapping[$sortBy], $desc ? 'DESC' : 'ASC');
        } else {
            $qb = $qb->orderBy('u.username', 'ASC');
        }

        if ($start !== null) {
            $qb = $qb->setMaxResults($max)
                ->setFirstResult($start);
            return new Paginator($qb, false);
        }
        return $qb->getQuery()
            ->getResult();
    }


    private function createDefaultQueryBuilder(bool $allowDeleted): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u');
        if (!$allowDeleted) {
            $qb->where('u.deletedAt IS NULL');
        }
        return $qb;
    }

    public function countAdministrators(): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.role = :admin')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('admin', User::ROLE_ADMIN)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function save(User $user): User
    {
        $this->_em->persist($user);
        return $user;
    }

    public function delete(User $user): void
    {
        $this->_em->remove($user);
    }

}
