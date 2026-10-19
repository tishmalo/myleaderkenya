<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCoordinatorsGuideRequest;
use App\Support\CoordinatorsGuide;
use Illuminate\Http\Request;

class CoordinatorsGuideController extends Controller
{
    public function index(Request $request)
    {
        $markdown = CoordinatorsGuide::document();
        $rendered = CoordinatorsGuide::render($markdown);

        return view('admin.guide.index', [
            'markdown' => $markdown,
            'html' => $rendered['html'],
            'headings' => $rendered['headings'],
            'isCustomised' => CoordinatorsGuide::isCustomised(),
            'canEdit' => (bool) $request->user()?->canAccess('guide.update'),
        ]);
    }

    public function update(UpdateCoordinatorsGuideRequest $request)
    {
        CoordinatorsGuide::save((string) $request->input('body'));

        return redirect()
            ->route('coordinators-guide.index')
            ->with('success', 'The coordinators guide was updated.');
    }
}
