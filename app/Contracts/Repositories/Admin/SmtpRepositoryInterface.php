<?php

namespace App\Contracts\Repositories\Admin;

interface SmtpRepositoryInterface
{
    /**
     * Write a single key=value pair to the .env file.
     * Creates the key if it doesn't exist yet.
     */
    public function setEnvironmentValue(string $key, string $value): void;
}
