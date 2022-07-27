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

    public function findOneByNDSR(string $name, string $domain, string $source, string $rcpt)
    {
        return $this->createDefaultQueryBuilder()
            ->andWhere('c.name = :name')
            ->andWhere('c.domain = :domain')
            ->andWhere('c.source = :source')
            ->andWhere('c.rcpt = :rcpt')
            ->setParameters(['name' => $name, 'domain' => $domain, 'source' => $source, 'rcpt' => $rcpt])
            ->getQuery()
            ->getResult();
    }

    public function findAll(User $user = null, string $query = null, string $start = null, string|int $max = 20, string $sortBy = null, bool $desc = false): iterable|Paginator
    {
        $count = null;

        $mapping = [
            'name' => 'c.name',
            'domain' => 'c.domain',
            'source' => 'c.source',
            'rcpt' => 'c.rcpt',
            'username' => 'u.username',
            'firstSeen' => 'c.firstSeen'
        ];

        $qb = $this->createDefaultQueryBuilder();

        if ($query) {
            $qb = $qb->andWhere('c.name LIKE :query OR c.domain LIKE :query OR c.source LIKE :query OR c.rcpt LIKE :query OR u.username LIKE :query OR c.firstSeen LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        if ($start !== null) {
            $countQb = clone $qb;
            $countQb->select('COUNT(c.domain)');
            $count = $countQb->getQuery()->getSingleScalarResult();
            $qb = $qb->setMaxResults($max)
                ->setFirstResult(intval($start) === 0 ? $start : (($start) * $max));
        }

        if ($sortBy !== null) {
            $qb = $qb->orderBy($mapping[$sortBy], $desc ? 'DESC' : 'ASC');
        } else {
            $qb = $qb->orderBy('c.domain', 'ASC');
        }

        $result = $qb->getQuery()->getArrayResult();

        if ($count === null) {
            $count = count($result);
        }

        return [
            'count' => $count,
            'results' => $result
        ];
    }

    private
    function createDefaultQueryBuilder(?User $user = null): QueryBuilder
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('c as connect', 'ua.aliasName', 'u.username', 'u.id as userID')
            ->from(Connect::class, 'c')
            ->leftJoin(UserAlias::class, 'ua', Join::WITH, 'ua.aliasName = c.rcpt')
            ->leftJoin(User::class, 'u', Join::WITH, 'u.id = ua.user');

        if ($user) {
            $qb = $qb->where('ua.user = :user')
                ->setParameter('user', $user);
        }

        return $qb;
    }


    public
    function deleteByDate(string $date): int
    {
        return $this->_em->createQueryBuilder()
            ->delete(Connect::class, 'c')
            ->where('DATE(c.firstSeen) <= :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }

    public
    function save(Connect $domain): Connect
    {
        $this->_em->persist($domain);
        $this->_em->flush();
        return $domain;
    }

    public
    function delete(Connect $domain): void
    {
        $this->_em->remove($domain);
        $this->_em->flush();
    }

}
