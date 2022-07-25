<?php

namespace App\Domain\Entity\UserAlias;

use App\Domain\Entity\HasId;
use App\Domain\Entity\User\User;
use App\Domain\Identifiable;
use App\Domain\UserAlias\Validator\UniqueEntry;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Webmozart\Assert\Assert;

#[ORM\Entity(repositoryClass: UserAliasRepository::class)]
#[ORM\Table(name: 'tq_aliases')]
#[ORM\UniqueConstraint(name: 'uniq_alias', columns: ['user_id', 'alias_name'])]
#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
#[Serializer\ReadOnlyProperty]
#[UniqueEntry]
class UserAlias implements Identifiable
{
    use HasId;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Serializer\Expose]
    #[Serializer\Type(User::class)]
    public User $user;

    #[ORM\Column(name: 'alias_name', type: 'string', length: 128)]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    public string $aliasName;

    public function __construct(
        string $id,
        User   $user,
        string $aliasName
    )
    {
        $this->user = $user;
        $this->setId($id)
            ->setAliasName($aliasName);
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $user = null): self
    {
        $this->user = $user;
        return $this;
    }

    public function getAliasName(): string
    {
        return $this->aliasName;
    }

    public function setAliasName(string $aliasName): self
    {
        Assert::lengthBetween($aliasName, 1, 128);
        Assert::email($aliasName);
        $this->aliasName = $aliasName;
        return $this;
    }
}
