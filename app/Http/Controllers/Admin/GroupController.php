<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Services\Admin\GroupService;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StoreGroupRequest;
use App\Http\Requests\Admin\SendGroupMessageRequest;

class GroupController extends Controller
{
    public function __construct(
        private GroupService $groupService
    ) {}

    /**
     * Show Create Group Form
     */
    public function create()
    {
        return view('groups.create');
    }

    /**
     * Store New Group
     */
    public function store(StoreGroupRequest $request)
    {

        $group = $this->groupService->createGroup(
            $request->validated(),
            auth()->id()
        );

        return redirect()->route('groups.show', $group)
                         ->with('success', 'Group created successfully!');
    }

    /**
     * Show Group Chat Room
     */
    public function show(Group $group)
    {
        $data = $this->groupService->getGroupIfMember($group, auth()->id());

        if (!$data) {
            abort(403, 'You are not a member of this group.');
        }

        return view('groups.show', ['group' => $data['group'], 'messages' => $data['messages']]);
    }

    /**
     * Send Message from Web
     */
    public function sendMessage(SendGroupMessageRequest $request, Group $group)
    {

        $success = $this->groupService->sendMessage(
            $group,
            $request->message,
            auth()->user()
        );

        if (!$success) {
            abort(403);
        }

        return redirect()->route('groups.show', $group)
                         ->with('success', 'Message sent successfully!');
    }
}