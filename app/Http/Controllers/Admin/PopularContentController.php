<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\NewsArticle;
use App\Models\PoliticalParty;
use App\Models\Poll;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * What the audience is actually reading: the most viewed stories, aspirants,
 * polls and parties, from the counters the public pages record.
 */
class PopularContentController extends Controller
{
    private const LIMIT = 10;

    public function index(): View
    {
        return view('admin.popular.index', [
            'articles' => $this->top(
                NewsArticle::query()->where('status', 'published'),
                'title',
                ['slug']
            ),
            'aspirants' => $this->top(
                Candidate::query()->where('approval_status', 'approved'),
                'name'
            ),
            'polls' => $this->top(Poll::query(), 'question', ['slug']),
            'parties' => $this->top(PoliticalParty::query(), 'name', ['slug']),
        ]);
    }

    /**
     * The most viewed rows of one table.
     *
     * @param  list<string>  $extraColumns
     * @return Collection<int, object>
     */
    private function top(Builder $query, string $labelColumn, array $extraColumns = []): Collection
    {
        if (! Schema::hasColumn($query->getModel()->getTable(), 'views')) {
            return collect();
        }

        return $query
            ->select(array_merge(['id', $labelColumn, 'views'], $extraColumns))
            ->orderByDesc('views')
            ->orderBy($labelColumn)
            ->limit(self::LIMIT)
            ->get();
    }
}
