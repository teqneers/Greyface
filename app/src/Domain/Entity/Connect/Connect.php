<?php

namespace App\Domain\Entity\Connect;

use App\Domain\Connect\Validator\UniqueEntry;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Webmozart\Assert\Assert as WebAssert;


#[ORM\Entity(repositoryClass: ConnectRepository::class)]
#[ORM\Table(name: 'connect')]
#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
#[Serializer\ReadOnlyProperty]
#[UniqueEntry]
class Connect
{
    #[ORM\Id]
    #[ORM\Column(name: 'sender_name', type: 'string', length: 128, nullable: false)]
    #[Assert\Type('string')]
    #[Assert\Length(max: 128)]
    #[Assert\NotBlank]
    #[Assert\Email(mode: 'strict')]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    public string $name = '';

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

    #[ORM\Column(name: 'rcpt', type: 'string', length: 128, nullable: false)]
    #[Assert\Type('string')]
    #[Assert\Length(max: 128)]
    #[Assert\NotBlank]
    #[Assert\Email(mode: 'strict')]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    public string $rcpt = '';

    #[ORM\Column(name: "first_seen", type: "datetime_immutable")]
    #[Serializer\Expose]
    #[Serializer\Type(DateTimeImmutable::class)]
    private ?DateTimeImmutable $firstSeen = null;

    public static function create(
        string $name,
        string $domain,
        string $source,
        string $rcpt
    ): self
    {
        return new self($name, $domain, $source, $rcpt);
    }

    private function __construct(string $name, string $domain, string $source, string $rcpt)
    {
        $this->setName($name)
            ->setDomain($domain)
            ->setSource($source)
            ->setRcpt($rcpt)
            ->setFirstSeen();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        WebAssert::lengthBetween($name, 1, 128);
        $this->name = $name;
        return $this;
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

    public function getRcpt(): string
    {
        return $this->rcpt;
    }

    public function setRcpt(string $rcpt): self
    {
        WebAssert::lengthBetween($rcpt, 1, 128);
        WebAssert::email($rcpt);
        $this->rcpt = $rcpt;
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
}
