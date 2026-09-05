<?php

namespace App\Tests\Domain\Entity\Connect;

use App\Domain\Connect\RecipientDelimiter;
use App\Domain\Entity\Connect\ConnectRepository;
use App\Test\ConnectTrait;
use App\Test\DatabaseTestTrait;
use App\Test\UserAliasTrait;
use App\Test\UserDomainTrait;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The delimiter is the MTA's setting, and Greyface cannot read main.cf, so it is
 * configuration. A site whose Postfix has no recipient_delimiter needs exact
 * matching back: there, anna+newsletter@example.com is a literal mailbox name
 * and has nothing necessarily to do with anna.
 *
 * The repository is built by hand here rather than fetched from the container,
 * because the container's copy carries whatever the environment configured and
 * the point is to pin both settings.
 */
class ConnectRepositoryTaggedRecipientTest extends KernelTestCase
{
    use DatabaseTestTrait, UserDomainTrait, UserAliasTrait, ConnectTrait;

    private function repositoryWithDelimiter(string $delimiter): ConnectRepository
    {
        return new ConnectRepository(
            self::getContainer()->get(ManagerRegistry::class),
            new RecipientDelimiter($delimiter)
        );
    }

    /**
     * @return string[]
     */
    private function recipientsVisibleTo(ConnectRepository $repository, object $user): array
    {
        $rows = $repository->findFiltered($user);

        $recipients = [];
        foreach ($rows['results'] as $row) {
            $recipients[] = $row['connect']['rcpt'];
        }

        return $recipients;
    }

    public function testTaggedRecipientsBelongToTheAddressTheyAreDeliveredTo(): void
    {
        self::bootKernel();

        $user = self::createUser();
        $alias = self::createUserAlias($user, 'info@greyface.de');
        $tagged = self::createConnect(rcpt: 'info+newsletter@greyface.de');
        self::initializeDatabaseWithEntities($user, $alias, $tagged);

        self::assertContains(
            'info+newsletter@greyface.de',
            $this->recipientsVisibleTo($this->repositoryWithDelimiter('+'), $user)
        );
    }

    public function testAnEmptyDelimiterRestoresExactMatching(): void
    {
        self::bootKernel();

        $user = self::createUser();
        $alias = self::createUserAlias($user, 'info@greyface.de');
        $tagged = self::createConnect(rcpt: 'info+newsletter@greyface.de');
        self::initializeDatabaseWithEntities($user, $alias, $tagged);

        $visible = $this->recipientsVisibleTo($this->repositoryWithDelimiter(''), $user);

        self::assertNotContains('info+newsletter@greyface.de', $visible);
        self::assertContains('info@greyface.de', $visible, 'the exact match must survive');
    }

    /**
     * The widening stops at the delivered address. A longer local part that
     * merely starts with the same letters is a different mailbox.
     */
    public function testAMailboxThatMerelySharesAPrefixDoesNotMatch(): void
    {
        self::bootKernel();

        $user = self::createUser();
        $alias = self::createUserAlias($user, 'info@greyface.de');
        $other = self::createConnect(rcpt: 'infodesk@greyface.de');
        self::initializeDatabaseWithEntities($user, $alias, $other);

        self::assertNotContains(
            'infodesk@greyface.de',
            $this->recipientsVisibleTo($this->repositoryWithDelimiter('+'), $user)
        );
    }
}
