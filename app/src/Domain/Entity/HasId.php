<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Webmozart\Assert\Assert;

#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
trait HasId
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'guid')]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    private string $id = '';

    public function getId(): string
    {
        return $this->id;
    }

    private function setId(string $id): self
    {
        Assert::uuid($id);
        $this->id = $id;
        return $this;
    }
}
