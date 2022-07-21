<?php

namespace App\Domain\Entity\OptOut\OptOutEmail;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @template-extends ServiceEntityRepository<OptOutEmail>
 */
class OptOutEmailRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, OptOutEmail::class);
    }

    public function findById(string $id): ?OptOutEmail
    {
        $optOutEmail = $this->find($id);
        if ($optOutEmail) {
            return $optOutEmail;
        }
        return null;
    }

    public function findByOptOutEmailName(string $optOutEmailName): ?OptOutEmail
    {
        $optOutEmail = $this->findOneBy(['email' => $optOutEmailName]);
        if ($optOutEmail) {
            return $optOutEmail;
        }
        return null;
    }


    public function findAll($start = null, $max = 20): iterable|Paginator
    {
        $qb = $this->createDefaultQueryBuilder()
            ->orderBy('d.email', 'ASC');
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
        $qb = $this->createQueryBuilder('d');
        return $qb;
    }

    public function save(OptOutEmail $optOutEmail): OptOutEmail
    {
        $this->_em->persist($optOutEmail);
        $this->_em->flush();
        return $optOutEmail;
    }

    public function delete(OptOutEmail $optOutEmail): void
    {
        $this->_em->remove($optOutEmail);
        $this->_em->flush();
    }

}
