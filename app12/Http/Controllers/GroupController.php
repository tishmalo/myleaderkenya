<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GroupController extends Controller
{
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
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        // Generate unique invite code
        do {
            $inviteCode = strtoupper(Str::random(8));
        } while (Group::where('invite_code', $inviteCode)->exists());

        $group = Group::create([
            'name'        => $request->name,
            'description' => $request->description,
            'created_by'  => auth()->id(),
            'invite_code' => $inviteCode,
        ]);

        // Add creator as member
        GroupMember::create([
            'group_id' => $group->id,
            'user_id'  => auth()->id(),
        ]);

        return redirect()->route('groups.show', $group)
                         ->with('success', 'Group created successfully!');
    }

    /**
     * Show Group Chat Room
     */
    public function show(Group $group)
    {
        $isMember = GroupMember::where('group_id', $group->id)
                               ->where('user_id', auth()->id())
                               ->exists();

        if (!$isMember) {
            abort(403, 'You are not a member of this group.');
        }

        $messages = GroupMessage::where('group_id', $group->id)
                                ->orderBy('created_at', 'asc')
                                ->get();

        return view('groups.show', compact('group', 'messages'));
    }

    /**
     * Send Message from Web
     */
    public function sendMessage(Request $request, Group $group)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $isMember = GroupMember::where('group_id', $group->id)
                               ->where('user_id', auth()->id())
                               ->exists();

        if (!$isMember) {
            abort(403);
        }

        GroupMessage::create([
            'group_id' => $group->id,
            'username' => auth()->user()->username ?? auth()->user()->name ?? 'Web User',
            'message'  => $request->message,
        ]);

        return redirect()->route('groups.show', $group)
                         ->with('success', 'Message sent successfully!');
    }
}