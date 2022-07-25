<?php

namespace App\Domain\Entity\Connect;

use App\Domain\Entity\User\User;
use App\Domain\Entity\UserAlias\UserAlias;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
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

    public function findOneByNDSR(string $name, string $domain, string $source, string $rcpt): ?Connect
    {
        return $this->createDefaultQueryBuilder('c')
            ->andWhere('c.name = :name')
            ->andWhere('c.domain = :domain')
            ->andWhere('c.source = :source')
            ->andWhere('c.rcpt = :rcpt')
            ->setParameters(['name' => $name, 'domain' => $domain, 'source' => $source, 'rcpt' => $rcpt])
            ->getQuery()
            ->getOneOrNullResult();
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


//    private function createDefaultQueryBuilder(): QueryBuilder
//    {
//        $qb = $this->_em->createQueryBuilder()
//            ->select('c.name', 'c.domain', 'c.source', 'c.rcpt', 'c.firstSeen', 'ua.aliasName', 'u.username', 'u.id as userID')
//            ->from(Connect::class, 'c')
//            ->leftJoin(UserAlias::class, 'ua', Join::WITH, 'ua.aliasName = c.rcpt')
//            ->leftJoin(User::class, 'u', Join::WITH, 'u.id = ua.user');
//        return $qb;
//    }

    private function createDefaultQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c')
            ->addSelect('ua', 'u')
            ->leftJoin('c.rcpt', 'ua')
            ->leftJoin('ua.user', 'u');
        return $qb;
    }

    public function deleteByDate(string $date): int
    {
        return $this->_em->createQueryBuilder()
            ->delete(Connect::class, 'c')
            ->where('DATE(c.firstSeen) <= :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
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
