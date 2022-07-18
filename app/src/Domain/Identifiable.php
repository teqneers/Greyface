<?php

namespace App\Domain;

/**
 * Interface Identifiable
 */
interface Identifiable
{
    /**
     * @return string
     */
    public function getId(): string;
}
