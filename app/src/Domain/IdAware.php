<?php

namespace App\Domain;

use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Trait IdAware
 *
 * @Serializer\ExclusionPolicy("all")
 */
trait IdAware
{
    /**
     * @Assert\Type("string")
     * @Assert\Uuid()
     * @Assert\NotBlank()
     *
     * @var string
     */
    private string $id = '';

    /**
     * @return string
     */
    private static function createUuid(): string
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * @param string $id
     */
    private function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return $this
     */
    public function setId(?string $id = null): self
    {
        $this->id = $id ?: self::createUuid();
        return $this;
    }
}
