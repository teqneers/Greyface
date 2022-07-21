<?php

namespace App\Domain\Entity\OptOut\OptOutDomain;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @template-extends ServiceEntityRepository<OptOutDomain>
 */
class OptOutDomainRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, OptOutDomain::class);
    }

    public function findById(string $id): ?OptOutDomain
    {
        $optOutDomain = $this->find($id);
        if ($optOutDomain) {
            return $optOutDomain;
        }
        return null;
    }

    public function findByOptOutDomainName(string $optOutDomainName): ?OptOutDomain
    {
        $optOutDomain = $this->findOneBy(['domain' => $optOutDomainName]);
        if ($optOutDomain) {
            return $optOutDomain;
        }
        return null;
    }


    public function findAll($start = null, $max = 20): iterable|Paginator
    {
        $qb = $this->createDefaultQueryBuilder()
            ->orderBy('d.domain', 'ASC');
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

    public function save(OptOutDomain $optOutDomain): OptOutDomain
    {
        $this->_em->persist($optOutDomain);
        $this->_em->flush();
        return $optOutDomain;
    }

    public function delete(OptOutDomain $optOutDomain): void
    {
        $this->_em->remove($optOutDomain);
        $this->_em->flush();
    }

}
