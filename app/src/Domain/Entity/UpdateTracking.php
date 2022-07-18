<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
#[Serializer\ReadOnlyProperty]
trait UpdateTracking
{
    #[ORM\Column(name: "updated_at", type: "datetime_immutable")]
    #[Gedmo\Timestampable(on: 'update')]
    #[Serializer\Expose]
    #[Serializer\Type(DateTimeImmutable::class)]
    private ?DateTimeImmutable $updatedAt = null;

    #[Gedmo\Blameable(on: 'update')]
    #[ORM\Column(name: "updated_by", type: "string", length: 128, nullable: true)]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    private ?string $updatedBy = null;

    protected function initUpdateTracking(): self
    {
        $this->updatedAt = new DateTimeImmutable('now');
        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }
}
