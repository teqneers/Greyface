<?php

namespace App\Domain\Entity\OptIn\OptInEmail;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @template-extends ServiceEntityRepository<OptInEmail>
 */
class OptInEmailRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, OptInEmail::class);
    }

    public function findById(string $id): ?OptInEmail
    {
        $optInEmail = $this->find($id);
        if ($optInEmail) {
            return $optInEmail;
        }
        return null;
    }

    public function findByOptInEmailName(string $optInEmailName): ?OptInEmail
    {
        $optInEmail = $this->findOneBy(['email' => $optInEmailName]);
        if ($optInEmail) {
            return $optInEmail;
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

    public function save(OptInEmail $optInEmail): OptInEmail
    {
        $this->_em->persist($optInEmail);
        $this->_em->flush();
        return $optInEmail;
    }

    public function delete(OptInEmail $optInEmail): void
    {
        $this->_em->remove($optInEmail);
        $this->_em->flush();
    }

}
