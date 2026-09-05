<?php

namespace App\Tests\Security\Voter;

use App\Domain\AutoWhiteList\DomainAutoWhiteList\Security\DomainAutoWhiteListVoter;
use App\Domain\AutoWhiteList\EmailAutoWhiteList\Security\EmailAutoWhiteListVoter;
use App\Domain\Connect\Security\ConnectVoter;
use App\Domain\Entity\AutoWhiteList\DomainAutoWhiteList\DomainAutoWhiteList;
use App\Domain\Entity\AutoWhiteList\EmailAutoWhiteList\EmailAutoWhiteList;
use App\Domain\Entity\Connect\Connect;
use App\Domain\Entity\OptIn\OptInDomain\OptInDomain;
use App\Domain\Entity\OptIn\OptInEmail\OptInEmail;
use App\Domain\Entity\OptOut\OptOutDomain\OptOutDomain;
use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmail;
use App\Domain\Entity\UserAlias\UserAlias;
use App\Domain\Entity\UserAlias\UserAliasRepository;
use App\Domain\OptIn\OptInDomain\Security\OptInDomainVoter;
use App\Domain\OptIn\OptInEmail\Security\OptInEmailVoter;
use App\Domain\OptOut\OptOutDomain\Security\OptOutDomainVoter;
use App\Domain\OptOut\OptOutEmail\Security\OptOutEmailVoter;
use App\Domain\User\Security\ChangePasswordVoter;
use App\Domain\UserAlias\Security\UserAliasVoter;
use App\Test\AutoWhiteListTrait;
use App\Test\ConnectTrait;
use App\Test\OptInOptOutTrait;
use App\Test\SecurityUserTrait;
use App\Test\UserAliasTrait;
use App\Test\UserDomainTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * The resource voters all share one shape: they gate every attribute behind
 * either "is an administrator" or "is any authenticated user", with no
 * per-resource rules. This pins that contract for all nine of them so a change
 * in Symfony's voter plumbing cannot silently open a resource up.
 *
 * App\Domain\User\Security\UserVoter is the one voter with real branching and
 * is covered separately in UserVoterTest.
 */
class ResourceVoterTest extends TestCase
{
    use UserDomainTrait, SecurityUserTrait, AutoWhiteListTrait, OptInOptOutTrait, UserAliasTrait, ConnectTrait;

    /**
     * @return iterable<string, array{Voter, string, string, object}>
     */
    public static function adminOnlyVoters(): iterable
    {
        $cases = [
            'auto-whitelist domain' => [
                new DomainAutoWhiteListVoter(), 'DOMAIN_AUTOWHITE', DomainAutoWhiteList::class,
                self::createAutoWhiteListDomain(),
            ],
            'auto-whitelist email' => [
                new EmailAutoWhiteListVoter(), 'EMAIL_AUTOWHITE', EmailAutoWhiteList::class,
                self::createAutoWhiteListEmail(),
            ],
            'opt-in domain' => [
                new OptInDomainVoter(), 'OPTIN_DOMAIN', OptInDomain::class, self::createOptInDomain(),
            ],
            'opt-in email' => [
                new OptInEmailVoter(), 'OPTIN_EMAIL', OptInEmail::class, self::createOptInEmail(),
            ],
            'opt-out domain' => [
                new OptOutDomainVoter(), 'OPTOUT_DOMAIN', OptOutDomain::class, self::createOptOutDomain(),
            ],
            'opt-out email' => [
                new OptOutEmailVoter(), 'OPTOUT_EMAIL', OptOutEmail::class, self::createOptOutEmail(),
            ],
            'user alias' => [
                new UserAliasVoter(), 'USER_ALIAS', UserAlias::class, self::createUserAlias(),
            ],
        ];

        foreach ($cases as $label => [$voter, $prefix, $subjectClass, $subject]) {
            foreach (['LIST', 'CREATE', 'SHOW', 'EDIT', 'DELETE'] as $action) {
                yield "$label / $action" => [$voter, $prefix . '_' . $action, $subjectClass, $subject];
            }
        }
    }

    #[DataProvider('adminOnlyVoters')]
    public function testGrantsToAdministrators(Voter $voter, string $attribute, string $subjectClass, object $subject): void
    {
        $token = self::createTokenForUser(self::createAdmin());

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $subject, [$attribute]),
            $attribute . ' should be granted to an administrator'
        );
    }

    #[DataProvider('adminOnlyVoters')]
    public function testDeniesToOrdinaryUsers(Voter $voter, string $attribute, string $subjectClass, object $subject): void
    {
        $token = self::createTokenForUser(self::createUser());

        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $subject, [$attribute]),
            $attribute . ' must not be granted to a non-administrator'
        );
    }

    #[DataProvider('adminOnlyVoters')]
    public function testDeniesToAnonymousTokens(Voter $voter, string $attribute, string $subjectClass, object $subject): void
    {
        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote(new NullToken(), $subject, [$attribute]),
            $attribute . ' must not be granted without authentication'
        );
    }

    #[DataProvider('adminOnlyVoters')]
    public function testDeclaresItsAttributeAndSubjectType(Voter $voter, string $attribute, string $subjectClass, object $subject): void
    {
        self::assertTrue($voter->supportsAttribute($attribute));
        self::assertTrue($voter->supportsType($subjectClass));
        self::assertTrue($voter->supportsType('null'));
        self::assertFalse($voter->supportsAttribute('NOT_AN_ATTRIBUTE'));
    }

    /**
     * The supports() guard: an attribute this voter does not own must make it
     * abstain, whatever the subject is. Reaching both halves of that guard needs
     * a correctly-typed subject and a foreign one.
     */
    #[DataProvider('adminOnlyVoters')]
    public function testAbstainsOnForeignAttributes(Voter $voter, string $attribute, string $subjectClass, object $subject): void
    {
        $token = self::createTokenForUser(self::createAdmin());

        self::assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($token, $subject, ['NOT_AN_ATTRIBUTE']),
            'a correctly typed subject with a foreign attribute must abstain'
        );
        self::assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($token, new \stdClass(), ['NOT_AN_ATTRIBUTE']),
            'a foreign subject with a foreign attribute must abstain'
        );
    }

    /**
     * USER_ALIAS_UNDELETE is declared as a supported attribute but has no case
     * in voteOnAttribute(), so it always falls through to a denial. Pinned here
     * because it looks like a granted permission from the outside.
     */
    public function testUserAliasUndeleteIsAlwaysDenied(): void
    {
        $voter = new UserAliasVoter();
        $token = self::createTokenForUser(self::createAdmin());

        self::assertTrue($voter->supportsAttribute('USER_ALIAS_UNDELETE'));
        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, self::createUserAlias(), ['USER_ALIAS_UNDELETE'])
        );
    }

    /**
     * The greylist is the one resource ordinary users may reach, so its voter is
     * the only one with a row-level rule: a user may act on mail addressed to an
     * address they own, and on nothing else.
     */
    private static function connectVoter(string ...$ownedAddresses): ConnectVoter
    {
        $aliases = self::createStub(UserAliasRepository::class);
        $aliases->method('findAliasNamesForUserId')->willReturn($ownedAddresses);

        return new ConnectVoter($aliases);
    }

    public static function connectRowAttributes(): iterable
    {
        yield 'show' => ['CONNECT_SHOW'];
        yield 'delete' => ['CONNECT_DELETE'];
        yield 'whitelist' => ['CONNECT_WHITELIST'];
    }

    #[DataProvider('connectRowAttributes')]
    public function testAdministratorsMayActOnAnyRow(string $attribute): void
    {
        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            self::connectVoter()->vote(
                self::createTokenForUser(self::createAdmin()),
                self::createConnect(rcpt: 'somebody-else@greyface.test'),
                [$attribute]
            )
        );
    }

    #[DataProvider('connectRowAttributes')]
    public function testUsersMayActOnMailAddressedToThemselves(string $attribute): void
    {
        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            self::connectVoter('mine@greyface.test')->vote(
                self::createTokenForUser(self::createUser()),
                self::createConnect(rcpt: 'mine@greyface.test'),
                [$attribute]
            )
        );
    }

    /**
     * The defect this voter was rewritten for. Every write endpoint takes its
     * identifiers from the request body, so a user who could name a row could
     * delete or whitelist it, including one the listing never showed them.
     */
    #[DataProvider('connectRowAttributes')]
    public function testUsersMayNotActOnSomebodyElsesMail(string $attribute): void
    {
        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            self::connectVoter('mine@greyface.test')->vote(
                self::createTokenForUser(self::createUser()),
                self::createConnect(rcpt: 'somebody-else@greyface.test'),
                [$attribute]
            )
        );
    }

    /**
     * The listing joins these two columns in the database under a _ci collation,
     * so matching case-sensitively here would show a user a row and then forbid
     * them from touching it.
     */
    public function testRecipientMatchingIgnoresCase(): void
    {
        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            self::connectVoter('Mine@Greyface.test')->vote(
                self::createTokenForUser(self::createUser()),
                self::createConnect(rcpt: 'mine@greyface.TEST'),
                ['CONNECT_DELETE']
            )
        );
    }

    public function testAnyAuthenticatedUserMayOpenTheListing(): void
    {
        foreach ([self::createUser(), self::createAdmin()] as $user) {
            self::assertSame(
                VoterInterface::ACCESS_GRANTED,
                self::connectVoter()->vote(self::createTokenForUser($user), null, ['CONNECT_LIST'])
            );
        }
    }

    /**
     * One request empties the greylist for every recipient on the server, so it
     * is not a row-level decision and never belongs to an ordinary user.
     */
    public function testDeletingByDateIsAdministratorsOnly(): void
    {
        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            self::connectVoter()->vote(
                self::createTokenForUser(self::createAdmin()),
                null,
                ['CONNECT_DELETE_BY_DATE']
            )
        );
        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            self::connectVoter('mine@greyface.test')->vote(
                self::createTokenForUser(self::createUser()),
                null,
                ['CONNECT_DELETE_BY_DATE']
            )
        );
    }

    #[DataProvider('connectRowAttributes')]
    public function testConnectIsDeniedToAnonymousTokens(string $attribute): void
    {
        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            self::connectVoter()->vote(new NullToken(), self::createConnect(), [$attribute])
        );
    }

    public function testConnectAbstainsOnForeignAttributes(): void
    {
        $voter = self::connectVoter();
        $token = self::createTokenForUser(self::createUser());

        self::assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($token, self::createConnect(), ['NOT_AN_ATTRIBUTE'])
        );
        self::assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($token, new \stdClass(), ['NOT_AN_ATTRIBUTE'])
        );
    }

    public function testChangePasswordAbstainsOnForeignAttributes(): void
    {
        self::assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            (new ChangePasswordVoter())->vote(
                self::createTokenForUser(self::createUser()),
                null,
                ['NOT_AN_ATTRIBUTE']
            )
        );
    }

    public function testChangePasswordIsGrantedToAnyAuthenticatedUser(): void
    {
        $voter = new ChangePasswordVoter();

        self::assertTrue($voter->supportsAttribute('CHANGE_MY_PASSWORD'));
        self::assertTrue($voter->supportsType('null'));

        self::assertSame(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote(self::createTokenForUser(self::createUser()), null, ['CHANGE_MY_PASSWORD'])
        );
        self::assertSame(
            VoterInterface::ACCESS_DENIED,
            $voter->vote(new NullToken(), null, ['CHANGE_MY_PASSWORD'])
        );
    }
}
