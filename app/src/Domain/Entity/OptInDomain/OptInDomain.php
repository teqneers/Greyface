<?php

namespace App\Domain\Entity\OptInDomain;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Webmozart\Assert\Assert as WebAssert;


#[ORM\Entity(repositoryClass: OptInDomainRepository::class)]
#[ORM\Table(name: 'optin_domain')]
#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
#[Serializer\ReadOnlyProperty]
class OptInDomain
{
    #[ORM\Id]
    #[ORM\Column(name: 'domain', type: 'string', length: 128, nullable: false)]
    #[Assert\Type('string')]
    #[Assert\Length(max: 128)]
    #[Assert\NotBlank]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    public string $domain = '';

    public static function create(
        string $domain
    ): self
    {
        return new self($domain);
    }

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
        WebAssert::lengthBetween($domain, 1, 128);
        $this->domain = $domain;
        return $this;
    }
}
