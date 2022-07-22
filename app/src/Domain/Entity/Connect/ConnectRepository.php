<?php

namespace App\Domain\Entity\Connect;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<Connect>
 */
class ConnectRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Connect::class);
    }

    public function findById(string $id): ?Connect
    {
        $domain = $this->find($id);
        if ($domain) {
            return $domain;
        }
        return null;
    }


    public function findAll($start = null, $max = 20): iterable|Paginator
    {
        $qb = $this->createDefaultQueryBuilder()
            ->orderBy('c.domain', 'ASC');
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
        $qb = $this->createQueryBuilder('c');
        return $qb;
    }

    public function save(Connect $domain): Connect
    {
        $this->_em->persist($domain);
        $this->_em->flush();
        return $domain;
    }

    public function delete(Connect $domain): void
    {
        $this->_em->remove($domain);
        $this->_em->flush();
    }

}
