<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CoalitionStoreRequest;
use App\Http\Requests\Admin\CoalitionUpdateRequest;
use App\Models\Coalition;
use App\Services\Admin\CoalitionService;
use Illuminate\Http\Request;

class CoalitionController extends Controller
{
    public function __construct(private CoalitionService $coalitionService) {}

    public function publicIndex()
{
    $coalitions = $this->coalitionService->getPublishedCoalitions(12);

    $seo = app(\App\Services\SeoService::class);
    $seo->setTitle('Kenya Political Coalitions 2027 | My Leader Kenya')
        ->setDescription('Explore Kenya political coalitions, their member parties and 2027 aspirants. Full coalition directory on My Leader Kenya.')
        ->setCanonical(route('coalitions.public'));

    return view('coalitions.public.index', compact('coalitions'));
}

public function publicShow(string $slug)
{
    $coalition = $this->coalitionService->getPublicShowData($slug);

    $seo = app(\App\Services\SeoService::class);

    $title = "{$coalition->name} Coalition | Kenya 2027";
    if (strlen($title) > 60) {
        $title = "{$coalition->name} | Kenya 2027 Coalition";
    }

    $description = \Illuminate\Support\Str::limit(strip_tags($coalition->description ?? ''), 155, '')
        ?: "View {$coalition->name} member parties and 2027 aspirants on My Leader Kenya.";

    $seo->setTitle($title)
        ->setDescription($description)
        ->setCanonical(route('coalitions.show', $coalition->slug))
        ->setOg([
            'title' => $title,
            'description' => $description,
            'image' => $coalition->logo_url ?? asset('images/myleader.png'),
        ]);

    return view('coalitions.public.show', compact('coalition'));
}

    public function index()
    {
        $filters = request()->only(['status', 'search']);
        $coalitions = $this->coalitionService->getPaginatedCoalitions($filters);

        return view('coalitions.index', compact('coalitions'));
    }

    public function create()
    {
        return view('coalitions.create', $this->coalitionService->getFormData());
    }

    public function store(CoalitionStoreRequest $request)
    {
        $this->coalitionService->createCoalition(
            $request->except(['logo', 'political_parties']),
            $request->file('logo'),
            $request->input('political_parties', [])
        );

        return redirect()->route('coalitions.index')
            ->with('success', 'Coalition created successfully!');
    }

    public function edit(Coalition $coalition)
    {
        $data = $this->coalitionService->getFormData();
        $data['coalition'] = $coalition->load('politicalParties');

        return view('coalitions.edit', $data);
    }

    public function update(CoalitionUpdateRequest $request, Coalition $coalition)
    {
        $this->coalitionService->updateCoalition(
            $coalition,
            $request->except(['logo', 'political_parties']),
            $request->file('logo'),
            $request->input('political_parties', [])
        );

        return redirect()->route('coalitions.index')
            ->with('success', 'Coalition updated successfully!');
    }

    public function destroy(Request $request, Coalition $coalition)
    {
        $this->coalitionService->deleteCoalition($coalition);

        $message = 'Coalition deleted successfully.';

        // A delete that arrives from a normal form post must not leave the
        // browser staring at a JSON payload; send the person back to the list.
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
    $coalitions = $this->coalitionService->getPaginatedCoalitions($filters, 10000);

    $headers = [
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="coalitions-' . now()->format('Y-m-d') . '.csv"',
    ];

    $callback = function () use ($coalitions) {
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, [
            'Name',
            'Slug',
            'Status',
            'Sort Order',
            'Member Parties',
            'Excerpt',
            'Created At',
        ]);

        foreach ($coalitions as $coalition) {
            fputcsv($out, [
                $coalition->name,
                $coalition->slug,
                $coalition->status,
                $coalition->sort_order,
                $coalition->politicalParties->pluck('name')->join(', '),
                $coalition->excerpt,
                $coalition->created_at?->format('Y-m-d H:i'),
            ]);
        }

        fclose($out);
    };

    return response()->stream($callback, 200, $headers);
}
}
