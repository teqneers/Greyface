<?php

namespace App\Domain\Entity\AutoWhiteList\DomainAutoWhiteList;

use App\Domain\AutoWhiteList\DomainAutoWhiteList\Validator\UniqueEntry;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Webmozart\Assert\Assert as WebAssert;


#[ORM\Entity(repositoryClass: DomainAutoWhiteListRepository::class)]
#[ORM\Table(name: 'domain_awl')]
#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
#[Serializer\ReadOnlyProperty]
#[UniqueEntry]
class DomainAutoWhiteList
{
    #[ORM\Id]
    #[ORM\Column(name: 'sender_domain', type: 'string', length: 128, nullable: false)]
    #[Assert\Type('string')]
    #[Assert\Length(max: 128)]
    #[Assert\NotBlank]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    public string $domain = '';

    #[ORM\Id]
    #[ORM\Column(name: 'src', type: 'string', length: 128, nullable: false)]
    #[Assert\Type('string')]
    #[Assert\Length(max: 128)]
    #[Assert\NotBlank]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    public string $source = '';

    #[ORM\Column(name: "first_seen", type: "datetime_immutable")]
    #[Serializer\Expose]
    #[Serializer\Type(DateTimeImmutable::class)]
    private ?DateTimeImmutable $firstSeen = null;

    #[ORM\Column(name: "last_seen", type: "datetime_immutable")]
    #[Serializer\Expose]
    #[Serializer\Type(DateTimeImmutable::class)]
    public ?DateTimeImmutable $lastSeen = null;

    public static function create(
        string $domain,
        string $source
    ): self
    {
        return new self($domain, $source);
    }

    private function __construct(string $domain, string $source)
    {
        $this->setDomain($domain)
            ->setSource($source)
            ->setFirstSeen();
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

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): self
    {
        WebAssert::lengthBetween($source, 1, 128);
        $this->source = $source;
        return $this;
    }

    public function getFirstSeen(): DateTimeImmutable
    {
        return $this->firstSeen;
    }

    public function setFirstSeen(): self
    {
        $this->firstSeen = new DateTimeImmutable('now');
        return $this;
    }

    public function getLastSeen(): DateTimeImmutable
    {
        return $this->lastSeen;
    }

    public function setLastSeen(): self
    {
        $this->lastSeen = new DateTimeImmutable('now');
        return $this;
    }
}
