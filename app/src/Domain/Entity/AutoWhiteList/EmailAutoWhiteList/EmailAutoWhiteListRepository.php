<?php

namespace App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<EmailAutoWhiteList>
 */
class EmailAutoWhiteListRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, EmailAutoWhiteList::class);
    }

    public function findById(string $id): ?EmailAutoWhiteList
    {
        $domain = $this->find($id);
        if ($domain) {
            return $domain;
        }
        return null;
    }


    public function findAll(string $query = null, string $start = null, string|int $max = 20, string $sortBy = null, bool $desc = false): iterable|Paginator
    {
        $mapping = [
            'name' => 'e.name',
            'domain' => 'e.domain',
            'source' => 'e.source',
            'firstSeen' => 'e.firstSeen',
            'lastSeen' => 'e.lastSeen'
        ];

        $qb = $this->createDefaultQueryBuilder();

        if ($query) {
            $qb = $qb->andWhere('e.name LIKE :query OR e.domain LIKE :query OR e.source LIKE :query OR e.firstSeen LIKE :query OR e.lastSeen LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }
        
        if ($sortBy !== null) {
            $qb = $qb->orderBy($mapping[$sortBy], $desc ? 'DESC' : 'ASC');
        } else {
            $qb = $qb->orderBy('e.name', 'ASC');
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
        $qb = $this->createQueryBuilder('e');
        return $qb;
    }

    public function save(EmailAutoWhiteList $domain): EmailAutoWhiteList
    {
        $this->_em->persist($domain);
        $this->_em->flush();
        return $domain;
    }

    public function delete(EmailAutoWhiteList $domain): void
    {
        $this->_em->remove($domain);
        $this->_em->flush();
    }

}
