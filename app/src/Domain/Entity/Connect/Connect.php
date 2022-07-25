<?php

namespace App\Domain\Entity\Connect;

use App\Domain\Connect\Validator\UniqueEntry;
use App\Domain\Entity\Project\ProjectUser;
use App\Domain\Entity\User\User;
use App\Domain\Entity\UserAlias\UserAlias;
use DateTimeImmutable;
use Doctrine\Common\Collections\Collection;
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

//    #[ORM\Id]
//    #[ORM\Column(name: 'rcpt', type: 'string', length: 128, nullable: false)]
//    #[Assert\Type('string')]
//    #[Assert\Length(max: 128)]
//    #[Assert\NotBlank]
//    #[Assert\Email(mode: 'strict')]
//    #[Serializer\Expose]
//    #[Serializer\Type('string')]
//    public string $rcpt = '';

    #[ORM\ManyToOne(targetEntity: UserAlias::class)]
    #[ORM\JoinColumn(name: 'rcpt', referencedColumnName: 'alias_name', nullable: false)]
    #[Serializer\Expose]
    #[Serializer\Type(UserAlias::class)]
    public UserAlias $rcpt;

    #[ORM\Column(name: "first_seen", type: "datetime_immutable")]
    #[Serializer\Expose]
    #[Serializer\Type(DateTimeImmutable::class)]
    public ?DateTimeImmutable $firstSeen = null;

    public static function create(
        string $name,
        string $domain,
        string $source,
        UserAlias $rcpt
    ): self
    {
        return new self($name, $domain, $source, $rcpt);
    }

    private function __construct(string $name, string $domain, string $source, UserAlias $rcpt)
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

    public function getRcpt(): UserAlias
    {
        return $this->rcpt;
    }

    public function setRcpt(UserAlias $rcpt): self
    {
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
