<?php

namespace App\Contracts\Repositories\Web;

use App\Models\PublicPulseMention;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PublicPulseMentionRepositoryInterface
{
    public function pendingClassification(int $limit, string $promptVersion, int $ttlDays): Collection;

    public function findMany(array $ids): Collection;

    public function paginateForAdmin(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function markClassified(PublicPulseMention $mention, array $attributes): void;

    public function normalizedContent(PublicPulseMention $mention): string;

    public function contentHash(PublicPulseMention $mention, string $promptVersion): string;
}
