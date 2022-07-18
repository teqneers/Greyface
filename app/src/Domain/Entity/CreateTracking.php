<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
#[Serializer\ReadOnlyProperty]
trait CreateTracking
{
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Serializer\Expose]
    #[Serializer\Type(DateTimeImmutable::class)]
    private ?DateTimeImmutable $createdAt = null;

    #[Gedmo\Blameable(on: 'create')]
    #[ORM\Column(name: 'created_by', type: 'string', length: 128, nullable: true)]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    private ?string $createdBy = null;

    protected function initCreateTracking(): self
    {
        $this->createdAt = new DateTimeImmutable('now');
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }
}
