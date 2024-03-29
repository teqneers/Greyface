<?php

namespace App\Domain\Entity\OptIn\OptInDomain;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @template-extends ServiceEntityRepository<OptInDomain>
 */
class OptInDomainRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, OptInDomain::class);
    }

    public function findById(string $id): ?OptInDomain
    {
        $optInDomain = $this->find($id);
        if ($optInDomain) {
            return $optInDomain;
        }
        return null;
    }

    public function findByOptInDomainName(string $optInDomainName): ?OptInDomain
    {
        $optInDomain = $this->findOneBy(['domain' => $optInDomainName]);
        if ($optInDomain) {
            return $optInDomain;
        }
        return null;
    }


    public function findAll(string $query = null, string $start = null, string|int $max = 20, string $sortBy = null, bool $desc = false): iterable|Paginator
    {
        $qb = $this->createDefaultQueryBuilder();

        if ($query) {
            $qb = $qb->andWhere('d.domain LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        if ($sortBy !== null) {
            $mapping = [
                'domain' => 'd.domain'
            ];
            $qb = $qb->orderBy($mapping[$sortBy], $desc ? 'DESC' : 'ASC');
        } else {
            $qb = $qb->orderBy('d.domain', 'ASC');
        }

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
        $qb = $this->createQueryBuilder('d');
        return $qb;
    }

    public function save(OptInDomain $optInDomain): OptInDomain
    {
        $this->_em->persist($optInDomain);
        $this->_em->flush();
        return $optInDomain;
    }

    public function delete(OptInDomain $optInDomain): void
    {
        $this->_em->remove($optInDomain);
        $this->_em->flush();
    }

}
