<?php

namespace App\Domain\Connect;

use App\Domain\Entity\AutoWhiteList\DomainAutoWhiteList\DomainAutoWhiteList;
use App\Domain\Entity\AutoWhiteList\DomainAutoWhiteList\DomainAutoWhiteListRepository;
use App\Domain\Entity\Connect\Connect;
use App\Domain\Entity\Connect\ConnectRepository;
use App\Domain\Entity\OptIn\OptInDomain\OptInDomain;
use App\Domain\Entity\OptIn\OptInDomain\OptInDomainRepository;
use App\Domain\Entity\OptIn\OptInEmail\OptInEmail;
use App\Domain\Entity\OptIn\OptInEmail\OptInEmailRepository;
use App\Domain\Entity\OptOut\OptOutDomain\OptOutDomain;
use App\Domain\Entity\OptOut\OptOutDomain\OptOutDomainRepository;
use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmail;
use App\Domain\Entity\OptOut\OptOutEmail\OptOutEmailRepository;

/**
 * Moves a greylisted entry onto one of SQLGrey's policy lists.
 *
 * The greylist row is removed either way, exactly as the auto-whitelist action
 * already does: the point of every one of these is that this delivery should
 * stop waiting, and the row is a record of waiting.
 *
 * Whether a list row was actually created is reported back, because an entry
 * may already be listed. Undo has to know: removing a row that was there before
 * the operator touched anything would quietly widen or narrow policy they never
 * set.
 */
final readonly class MoveConnectToList
{
    public function __construct(
        private ConnectRepository             $connects,
        private DomainAutoWhiteListRepository $domainAutoWhiteList,
        private OptOutEmailRepository         $whitelistEmails,
        private OptOutDomainRepository        $whitelistDomains,
        private OptInEmailRepository          $blacklistEmails,
        private OptInDomainRepository         $blacklistDomains,
        private SenderAddress                 $senderAddress
    ) {
    }

    /**
     * @return bool whether a list row was created, as opposed to already present
     */
    public function move(Connect $entry, ListTarget $target): bool
    {
        $created = $this->addTo($entry, $target);
        $this->connects->delete($entry);

        return $created;
    }

    /**
     * Puts back a greylist entry and, if this move created one, removes the list
     * row again.
     */
    public function undo(Connect $entry, ListTarget $target, bool $created): void
    {
        if (!$this->connects->find([
            'name' => $entry->getName(),
            'domain' => $entry->getDomain(),
            'source' => $entry->getSource(),
            'rcpt' => $entry->getRcpt(),
        ])) {
            $this->connects->save($entry);
        }

        if ($created) {
            $this->removeFrom($entry, $target);
        }
    }

    private function addTo(Connect $entry, ListTarget $target): bool
    {
        $email = $this->senderAddress->of($entry);
        $domain = $entry->getDomain();

        switch ($target) {
            case ListTarget::AutoWhitelistDomain:
                if ($this->domainAutoWhiteList->find(['domain' => $domain, 'source' => $entry->getSource()])) {
                    return false;
                }
                $this->domainAutoWhiteList->save(DomainAutoWhiteList::create(
                    $domain,
                    $entry->getSource(),
                    $entry->getFirstSeen(),
                    $entry->getFirstSeen()
                ));
                return true;

            case ListTarget::WhitelistEmail:
                if ($this->whitelistEmails->findByOptOutEmailName($email)) {
                    return false;
                }
                $this->whitelistEmails->save(OptOutEmail::create($email));
                return true;

            case ListTarget::WhitelistDomain:
                if ($this->whitelistDomains->find($domain)) {
                    return false;
                }
                $this->whitelistDomains->save(OptOutDomain::create($domain));
                return true;

            case ListTarget::BlacklistEmail:
                if ($this->blacklistEmails->find($email)) {
                    return false;
                }
                $this->blacklistEmails->save(OptInEmail::create($email));
                return true;

            case ListTarget::BlacklistDomain:
                if ($this->blacklistDomains->find($domain)) {
                    return false;
                }
                $this->blacklistDomains->save(OptInDomain::create($domain));
                return true;
        }
    }

    private function removeFrom(Connect $entry, ListTarget $target): void
    {
        $email = $this->senderAddress->of($entry);
        $domain = $entry->getDomain();

        $row = match ($target) {
            ListTarget::AutoWhitelistDomain => $this->domainAutoWhiteList->find(
                ['domain' => $domain, 'source' => $entry->getSource()]
            ),
            ListTarget::WhitelistEmail => $this->whitelistEmails->findByOptOutEmailName($email),
            ListTarget::WhitelistDomain => $this->whitelistDomains->find($domain),
            ListTarget::BlacklistEmail => $this->blacklistEmails->find($email),
            ListTarget::BlacklistDomain => $this->blacklistDomains->find($domain),
        };

        if ($row === null) {
            return;
        }

        match ($target) {
            ListTarget::AutoWhitelistDomain => $this->domainAutoWhiteList->delete($row),
            ListTarget::WhitelistEmail => $this->whitelistEmails->delete($row),
            ListTarget::WhitelistDomain => $this->whitelistDomains->delete($row),
            ListTarget::BlacklistEmail => $this->blacklistEmails->delete($row),
            ListTarget::BlacklistDomain => $this->blacklistDomains->delete($row),
        };
    }
}
