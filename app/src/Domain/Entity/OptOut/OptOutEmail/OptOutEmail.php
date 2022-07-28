<?php

namespace App\Domain\Entity\OptOut\OptOutEmail;

use App\Domain\OptOut\OptOutEmail\Validator\UniqueEmail;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Webmozart\Assert\Assert as WebAssert;


#[ORM\Entity(repositoryClass: OptOutEmailRepository::class)]
#[ORM\Table(name: 'optout_email')]
#[Serializer\ExclusionPolicy(Serializer\ExclusionPolicy::ALL)]
#[Serializer\ReadOnlyProperty]
class OptOutEmail
{
    #[ORM\Id]
    #[ORM\Column(name: 'email', type: 'string', length: 128, unique: true, nullable: false)]
    #[Assert\Type('string')]
    #[Assert\Length(max: 128)]
    #[Assert\NotBlank]
    #[UniqueEmail]
    #[Assert\Email(mode: 'strict')]
    #[Serializer\Expose]
    #[Serializer\Type('string')]
    public string $email = '';

    public static function create(
        string $email
    ): self
    {
        return new self($email);
    }

    public function __construct(string $email)
    {
        $this->setEmail($email);
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        WebAssert::lengthBetween($email, 1, 128);
        WebAssert::email($email);
        $this->email = $email;
        return $this;
    }
}
