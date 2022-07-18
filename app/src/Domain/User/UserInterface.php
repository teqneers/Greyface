<?php

namespace App\Domain\User;

use App\Domain\Identifiable;

interface UserInterface extends Identifiable
{
    public function getUsername(): string;

    public function getEmail(): string;

    public function isAdministrator(): bool;

    public function equals(UserInterface $other): bool;
}
