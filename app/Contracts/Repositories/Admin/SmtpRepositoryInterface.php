<?php

namespace App\Contracts\Repositories\Admin;

interface SmtpRepositoryInterface
{
    /**
     * Write a single key=value pair to the .env file.
     * Creates the key if it doesn't exist yet.
     * A null value is written as an empty string.
     */
    public function setEnvironmentValue(string $key, ?string $value): void;
}
