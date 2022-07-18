<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
#[Serializer\ReadOnlyProperty]
trait DeleteTracking
{
    #[ORM\Column(name: 'deleted_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $deletedAt = null;

    #[Serializer\VirtualProperty]
    #[Serializer\SerializedName('is_deleted')]
    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function delete(?DateTimeImmutable $deletedAt = null): self
    {
        $this->deletedAt = $deletedAt ?? new DateTimeImmutable('now');
        return $this;
    }

    public function undelete(): self
    {
        $this->deletedAt = null;
        return $this;
    }
}
