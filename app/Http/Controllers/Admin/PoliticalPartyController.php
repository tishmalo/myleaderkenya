<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PoliticalPartyStoreRequest;
use App\Http\Requests\Admin\PoliticalPartyUpdateRequest;
use App\Models\PoliticalParty;
use App\Services\Admin\PoliticalPartyService;
use App\Services\SeoService;
use App\Services\Web\ViewTracker;
use Illuminate\Http\Request;

class PoliticalPartyController extends Controller
{
    public function __construct(
        private PoliticalPartyService $politicalPartyService,
        private ViewTracker $viewTracker,
        private SeoService $seo,
    ) {}

    public function publicIndex()
{
    $politicalParties = $this->politicalPartyService->getPublishedParties(12);

    $this->seo->setTitle('Kenya Political Parties 2027 | My Leader Kenya')
        ->setDescription('Browse all Kenya political parties, their aspirants, leadership and 2027 election profiles. Full party directory on My Leader Kenya.')
        ->setCanonical(route('parties.public'));

    return view('political-parties.public.index', compact('politicalParties'));
}

public function publicShow(string $slug)
{
    $data = $this->politicalPartyService->getPublicPageData($slug, 20);
    $party = $data['politicalParty'];

    $this->viewTracker->track($party, request());


    $title = "{$party->name} – Kenya Political Party | 2027";
    if (strlen($title) > 60) {
        $title = "{$party->name} | Kenya 2027 Party";
    }

    $description = \Illuminate\Support\Str::limit(strip_tags($party->description ?? ''), 155, '')
        ?: "View {$party->name} aspirants, leadership and 2027 election information on My Leader Kenya.";

    $this->seo->setTitle($title)
        ->setDescription($description)
        ->setCanonical(route('parties.show', $party->slug))
        ->setOg([
            'title' => $title,
            'description' => $description,
            'type' => 'website',
            'image' => $party->logo_url ?? asset('images/myleader.png'),
        ]);

    return view('political-parties.public.show', $data);
}

    public function index()
    {
        $filters = request()->only(['status', 'search']);
        $politicalParties = $this->politicalPartyService->getPaginatedParties($filters);

        return view('political-parties.index', compact('politicalParties'));
    }

    public function create()
    {
        return view('political-parties.create');
    }

    public function store(PoliticalPartyStoreRequest $request)
    {
        $this->politicalPartyService->createParty(
            $request->except(['logo']),
            $request->file('logo')
        );

        return redirect()->route('political-parties.index')
            ->with('success', 'Political party created successfully!');
    }

    public function edit(PoliticalParty $politicalParty)
    {
        return view('political-parties.edit', compact('politicalParty'));
    }

    public function update(PoliticalPartyUpdateRequest $request, PoliticalParty $politicalParty)
    {
        $this->politicalPartyService->updateParty(
            $politicalParty,
            $request->except(['logo']),
            $request->file('logo')
        );

        return redirect()->route('political-parties.index')
            ->with('success', 'Political party updated successfully!');
    }

    public function destroy(Request $request, PoliticalParty $politicalParty)
    {
        $this->politicalPartyService->deleteParty($politicalParty);

        $message = 'Political party deleted successfully.';

        if (! $request->expectsJson()) {
            return redirect()->back()->with('success', $message);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
    public function export()
{
    $filters = request()->only(['status', 'search']);
    $parties = $this->politicalPartyService->getPaginatedParties($filters, 10000); // high limit for export

    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="political-parties-' . now()->format('Y-m-d') . '.csv"',
    ];

    $callback = function () use ($parties) {
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel

        fputcsv($out, [
            'Name',
            'Abbreviation',
            'Slug',
            'Status',
            'Sort Order',
            'Aspirants Count',
            'Website',
            'Phone 1',
            'Phone 2',
            'Email 1',
            'Email 2',
            'Excerpt',
            'Created At',
        ]);

        foreach ($parties as $party) {
            fputcsv($out, [
                $party->name,
                $party->abbreviation,
                $party->slug,
                $party->status,
                $party->sort_order,
                $party->candidates_count ?? 0,
                $party->website_url,
                $party->phone_1,
                $party->phone_2,
                $party->email_1,
                $party->email_2,
                $party->excerpt,
                $party->created_at?->format('Y-m-d H:i'),
            ]);
        }

        fclose($out);
    };

    return response()->stream($callback, 200, $headers);
}

}
