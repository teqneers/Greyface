<?php

namespace App\Domain\Entity\AutoWhiteList\DomainAutoWhiteList;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @template-extends ServiceEntityRepository<DomainAutoWhiteList>
 */
class DomainAutoWhiteListRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, DomainAutoWhiteList::class);
    }

    public function findById(string $id): ?DomainAutoWhiteList
    {
        $domain = $this->find($id);
        if ($domain) {
            return $domain;
        }
        return null;
    }

    public function findAll(string $query = null, string $start = null, string|int $max = 20, string $sortBy = null, bool $desc = false): iterable|Paginator
    {
        $qb = $this->createDefaultQueryBuilder();

        if ($query) {
            $qb = $qb->andWhere('d.domain LIKE :query OR d.source LIKE :query OR d.firstSeen LIKE :query OR d.lastSeen LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        if ($sortBy !== null) {
            $mapping = [
                'domain' => 'd.domain',
                'source' => 'd.source',
                'firstSeen' => 'd.firstSeen',
                'lastSeen' => 'd.lastSeen'
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

    public function save(DomainAutoWhiteList $domain): DomainAutoWhiteList
    {
        $this->_em->persist($domain);
        $this->_em->flush();
        return $domain;
    }

    public function delete(DomainAutoWhiteList $domain): void
    {
        $this->_em->remove($domain);
        $this->_em->flush();
    }

}
