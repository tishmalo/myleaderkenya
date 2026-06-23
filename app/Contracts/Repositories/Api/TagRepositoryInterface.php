<?php

namespace App\Contracts\Repositories\Api;

use Illuminate\Support\Collection;

interface TagRepositoryInterface
{
    public function all(): Collection;
}
