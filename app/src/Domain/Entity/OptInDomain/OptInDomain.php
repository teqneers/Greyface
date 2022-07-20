<?php

namespace App\Domain\Entity\OptInDomain;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Webmozart\Assert\Assert;

#[ORM\Entity(repositoryClass: OptInDomainRepository::class)]
#[ORM\Table(name: 'optin_domain')]
#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
#[Serializer\ReadOnlyProperty]
class OptInDomain
{
    #[ORM\Id]
    #[ORM\Column(name: 'domain', type: 'string', length: 128)]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    private string $domain = '';

    private function __construct(string $domain)
    {
        $this->setDomain($domain);
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        Assert::lengthBetween($domain, 1, 128);
        $this->domain = $domain;
        return $this;
    }
}
