<?php

namespace App\Domain\Entity\User;

use App\Domain\User\UserInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * @return User[]
     */
    public function findAll(bool $allowDeleted = false): array
    {
        return $this->createDefaultQueryBuilder($allowDeleted)
                    ->orderBy('u.username', 'ASC')
                    ->getQuery()
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
