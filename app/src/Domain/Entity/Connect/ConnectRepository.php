<?php

namespace App\Domain\Entity\Connect;

use App\Domain\Entity\User\User;
use App\Domain\Entity\UserAlias\UserAlias;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\ArrayShape;

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
            // QueryBuilder::setParameters() takes only an ArrayCollection in ORM 3;
            // individual setParameter() calls are equivalent and clearer.
            ->setParameter('name', $name)
            ->setParameter('domain', $domain)
            ->setParameter('source', $source)
            ->setParameter('rcpt', $rcpt)
            ->getQuery()
            ->getResult();
    }

    #[ArrayShape(['count' => "mixed", 'results' => "mixed"])]
    public function findFiltered(User|string|null $user = null, ?string $query = null, ?string $start = null, string|int $max = 20, ?string $sortBy = null, bool $desc = false, ?string $before = null): iterable|Paginator
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

        $qb = $this->createDefaultQueryBuilder($user);

        if ($query) {
            $qb = $qb->andWhere('c.name LIKE :query OR c.domain LIKE :query OR c.source LIKE :query OR c.rcpt LIKE :query OR u.username LIKE :query OR c.firstSeen LIKE :query')
                ->setParameter('query', '%' . $query . '%');
        }

        // Same cut-off as deleteByDate(), so the UI can show how many rows a
        // delete-by-date would remove before it is confirmed.
        if ($before) {
            $qb = $qb->andWhere('DATE(c.firstSeen) <= :before')
                ->setParameter('before', $before);
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
    function createDefaultQueryBuilder(User|string|null $user = null): QueryBuilder
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('c as connect', 'ua.aliasName', 'u.username', 'u.id as userID')
            ->from(Connect::class, 'c')
            ->leftJoin(UserAlias::class, 'ua', Join::WITH, 'ua.aliasName = c.rcpt')
            ->leftJoin(User::class, 'u', Join::WITH, 'u.id = ua.user');

        if ($user) {
            if ($user === 'show_unassigned') {
                $qb = $qb->where('ua.user IS NULL');
            } else {
                $qb = $qb->where('ua.user = :user')
                    ->setParameter('user', $user);
            }
        }

        return $qb;
    }


    public
    function deleteByDate(string $date): int
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->delete(Connect::class, 'c')
            ->where('DATE(c.firstSeen) <= :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }

    public
    function save(Connect $domain): Connect
    {
        $this->getEntityManager()->persist($domain);
        $this->getEntityManager()->flush();
        return $domain;
    }

    public
    function delete(Connect $domain): void
    {
        $this->getEntityManager()->remove($domain);
        $this->getEntityManager()->flush();
    }

}
