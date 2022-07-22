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
