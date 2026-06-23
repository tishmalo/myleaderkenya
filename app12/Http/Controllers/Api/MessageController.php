<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\PollingStation; 
use App\Models\Group;          // ← NEW
use App\Models\GroupMember;   // ← NEW
use App\Models\GroupMessage;  // ← NEW
use Illuminate\Support\Str;
use App\Models\MessageReaction;

class MessageController extends Controller
{
    public function index()
{
    $messages = Message::latest()->take(50)->get();   // Public constituency messages

    $groups = auth()->user()
        ->groups()                    // Assuming you have relationship
        ->withCount('members')
        ->latest()
        ->get();

    return view('messages.index', compact('messages', 'groups'));
}
   
    public function getCounties()
    {
        $counties = [
            'Mombasa', 'Kwale', 'Kilifi', 'Tana River', 'Lamu', 'Taita Taveta',
            'Garissa', 'Wajir', 'Mandera', 'Marsabit', 'Isiolo', 'Meru',
            'Tharaka Nithi', 'Embu', 'Kitui', 'Machakos', 'Makueni', 'Nyandarua',
            'Nyeri', 'Kirinyaga', 'Murang\'a', 'Kiambu', 'Turkana', 'West Pokot',
            'Samburu', 'Trans Nzoia', 'Uasin Gishu', 'Elgeyo Marakwet', 'Nandi',
            'Baringo', 'Laikipia', 'Nakuru', 'Narok', 'Kajiado', 'Kericho',
            'Bomet', 'Kakamega', 'Vihiga', 'Bungoma', 'Busia', 'Siaya', 'Kisumu',
            'Homa Bay', 'Migori', 'Kisii', 'Nyamira', 'Nairobi'
        ];

        return response()->json($counties);
    }

public function sendMessage(Request $request)
{
    $user = $request->user();

    // Safety check - if no authenticated user
    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated. Please login first.'
        ], 401);
    }

    $request->validate([
        'message'      => 'required|string|max:500',
        'county'       => 'required|string|max:100',
        'constituency' => 'required|string|max:100',
        'latitude'     => 'nullable|numeric|between:-90,90',
        'longitude'    => 'nullable|numeric|between:-180,180',
    ]);

    Message::create([
        'username'     => $user->username ?? $user->name ?? 'Anonymous',  // Fallback
        'message'      => $request->message,
        'county'       => $request->county,
        'constituency' => $request->constituency,
        'latitude'     => $request->latitude,
        'longitude'    => $request->longitude,
    ]);

    return response()->json([
        'message' => 'Message sent successfully to ' . $request->constituency . ' constituency'
    ], 201);
}
    /**
     * 4. Get Messages for a Constituency Chatroom (Public)
     */
    /**
 * Get Constituencies for a selected County
 */
    public function getConstituencies(Request $request)
        {
            $county = trim($request->query('county'));

            $data = [
                'Mombasa'       => ['Changamwe', 'Jomvu', 'Kisauni', 'Likoni', 'Mvita', 'Nyali'],
                'Kwale'         => ['Kinango', 'Lunga Lunga', 'Matuga', 'Msambweni'],
                'Kilifi'        => ['Ganze', 'Kaloleni', 'Kilifi North', 'Kilifi South', 'Malindi', 'Rabai'],
                'Tana River'    => ['Bura', 'Galole', 'Garsen'],
                'Lamu'          => ['Lamu East', 'Lamu West'],
                'Taita Taveta'  => ['Mwatate', 'Taveta', 'Voi', 'Wundanyi'],
                'Garissa'       => ['Dadaab', 'Fafi', 'Garissa Township', 'Lagdera'],
                'Wajir'         => ['Eldas', 'Tarbaj', 'Wajir East', 'Wajir North', 'Wajir South', 'Wajir West'],
                'Mandera'       => ['Banissa', 'Lafey', 'Mandera East', 'Mandera North', 'Mandera South', 'Mandera West'],
                'Marsabit'      => ['Chalbi', 'Laisamis', 'Moyale', 'North Horr', 'Saku'],
                'Isiolo'        => ['Garbatulla', 'Isiolo', 'Merti'],
                'Meru'          => ['Buuri', 'Central Imenti', 'Igembe Central', 'Igembe North', 'Igembe South', 'North Imenti', 'South Imenti', 'Tigania East', 'Tigania West'],
                'Tharaka Nithi' => ['Chuka', 'Maara', 'Tharaka'],
                'Embu'          => ['Manyatta', 'Mbeere North', 'Mbeere South', 'Runyenjes'],
                'Kitui'         => ['Kitui Central', 'Kitui East', 'Kitui Rural', 'Kitui South', 'Kitui West', 'Mwingi Central', 'Mwingi North', 'Mwingi West'],
                'Machakos'      => ['Kangundo', 'Kathiani', 'Machakos Town', 'Masinga', 'Matungulu', 'Mavoko', 'Mwala'],
                'Makueni'       => ['Kaiti', 'Kibwezi East', 'Kibwezi West', 'Kilome', 'Makueni', 'Mbooni'],
                'Nyandarua'     => ['Kinangop', 'Kipipiri', 'Ndaragwa', 'Ol Jorok', 'Ol Kalou'],
                'Nyeri'         => ['Kieni', 'Mathira East', 'Mathira West', 'Mukurweini', 'Nyeri Town', 'Othaya', 'Tetu'],
                'Kirinyaga'     => ['Gichugu', 'Kirinyaga Central', 'Mwea'],
                "Murang'a"      => ['Gatanga', 'Kahuro', 'Kangema', 'Kigumo', 'Kiharu', 'Mathioya'],
                'Kiambu'        => ['Gatundu North', 'Gatundu South', 'Githunguri', 'Juja', 'Kabete', 'Kiambaa', 'Kiambu', 'Kikuyu', 'Limuru', 'Ruiru', 'Thika Town'],
                'Turkana'       => ['Loima', 'Turkana Central', 'Turkana East', 'Turkana North', 'Turkana South', 'Turkana West'],
                'West Pokot'    => ['Kacheliba', 'Kapenguria', 'Konyar', 'Pokot South'],
                'Samburu'       => ['Samburu East', 'Samburu North', 'Samburu West'],
                'Trans Nzoia'   => ['Cherangany', 'Endebess', 'Kiminin', 'Kwanza', 'Saboti'],
                'Uasin Gishu'   => ['Ainabkoi', 'Kapseret', 'Kesses', 'Soy', 'Turbo'],
                'Elgeyo Marakwet'=> ['Keiyo North', 'Keiyo South', 'Marakwet East', 'Marakwet West'],
                'Nandi'         => ['Aldai', 'Chesumei', 'Emgwen', 'Mosop', 'Nandi Hills', 'Tinderet'],
                'Baringo'       => ['Baringo Central', 'Baringo East', 'Baringo North', 'Baringo South', 'Mogotio', 'Tiaty'],
                'Laikipia'      => ['Laikipia East', 'Laikipia North', 'Laikipia West'],
                'Nakuru'        => ['Bahati', 'Gilgil', 'Kuresoi North', 'Kuresoi South', 'Molo', 'Naivasha', 'Nakuru Town East', 'Nakuru Town West', 'Njoro', 'Rongai', 'Subukia'],
                'Narok'         => ['Emurua Dikirr', 'Kilgoris', 'Narok East', 'Narok North', 'Narok South', 'Narok West'],
                'Kajiado'       => ['Kajiado Central', 'Kajiado East', 'Kajiado North', 'Kajiado South', 'Kajiado West'],
                'Kericho'       => ['Ainamoi', 'Belgut', 'Bureti', 'Kipkelion East', 'Kipkelion West', 'Sigowet-Soin'],
                'Bomet'         => ['Bomet Central', 'Bomet East', 'Chepalungu', 'Konoin', 'Sotik'],
                'Kakamega'      => ['Butere', 'Ikolomani', 'Kakamega Central', 'Kakamega East', 'Kakamega North', 'Kakamega South', 'Khwisero', 'Lugari', 'Lurambi', 'Malava', 'Shinyalu'],
                'Vihiga'        => ['Emuhaya', 'Hamisi', 'Luanda', 'Sabatia', 'Vihiga'],
                'Bungoma'       => ['Bumula', 'Kabuchai', 'Kanduyi', 'Kimilili', 'Mt. Elgon', 'Sirisia', 'Tongaren', 'Webuye East', 'Webuye West'],
                'Busia'         => ['Budalangi', 'Butula', 'Funyula', 'Matayos', 'Nambale', 'Teso North', 'Teso South'],
                'Siaya'         => ['Alego Usonga', 'Bondo', 'Gem', 'Rarieda', 'Ugenya', 'Ugunja'],
                'Kisumu'        => ['Kisumu Central', 'Kisumu East', 'Kisumu West', 'Muhoroni', 'Nyakach', 'Nyando', 'Seme'],
                'Homa Bay'      => ['Homa Bay Town', 'Karachuonyo', 'Kasipul', 'Kabondo Kasipul', 'Ndhiwa', 'Rangwe', 'Suba'],
                'Migori'        => ['Awendo', 'Kuria East', 'Kuria West', 'Migori', 'Nyatike', 'Rongo', 'Suna East', 'Suna West'],
                'Kisii'         => ['Bobasi', 'Bomachoge Borabu', 'Bomachoge Chache', 'Nyaribari Chache', 'Nyaribari Masaba', 'South Mugirango'],
                'Nyamira'       => ['Borabu', 'Kitutu Masaba', 'North Mugirango', 'West Mugirango'],
                'Nairobi'       => ['Westlands', 'Dagoretti North', 'Dagoretti South', "Lang'ata", 'Kibra', 'Roysambu', 'Kasarani', 'Ruaraka', 'Embakasi South', 'Embakasi North', 'Embakasi Central', 'Embakasi East', 'Embakasi West', 'Makadara', 'Kamukunji', 'Starehe', 'Mathare'],
            ];

            return response()->json($data[$county] ?? []);
        }

    public function getConstituencyMessages(Request $request)
{
    $request->validate([
        'county'       => 'required|string',
        'constituency' => 'required|string',
    ]);

    $messages = Message::inConstituency($request->county, $request->constituency)
        ->get(['username', 'message', 'latitude', 'longitude', 'created_at']);

    return response()->json($messages);
}

    public function nearbyMessages(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $messages = Message::nearby(
            $request->latitude,
            $request->longitude,
            500
        )->get(['username', 'message', 'latitude', 'longitude', 'created_at']);

        return response()->json($messages);
    }

    /**
     * 3. Add/Update Voter Status (protected)
     */
    public function updateVoterStatus(Request $request)
{
    $user = $request->user();

    $request->validate([
        'is_voter' => 'required|boolean',
        'county'   => 'required|string|max:100',
        'age'      => 'required|integer|min:18|max:120',
        'gender'   => 'required|in:male,female,other',
    ]);

    $user->update([
        'is_voter' => $request->is_voter,
        'county'   => $request->county,
        'age'      => $request->age,
        'gender'   => $request->gender,
    ]);

    return response()->json([
        'message' => 'Voter status updated successfully',
        'status'  => $user->only(['is_voter', 'county', 'age', 'gender'])
    ]);
}

    /**
     * 4. Retrieve Voter Status (protected)
     */
    public function getVoterStatus(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'is_voter' => $user->is_voter,
            'county'   => $user->county,
            'age'      => $user->age,
        ]);
    }

    // get all voters
    public function getAllVoters()
    {
        $voters = User::select('username', 'county', 'age', 'gender', 'is_voter', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($voters);
    }

        /**
     * POST /api/stations - Add new polling station (from app or admin)
     */
    public function addStation(Request $request)
    {
        $request->validate([
            'county'          => 'required|string|max:100',
            'constituency'    => 'required|string|max:100',
            'office'          => 'required|string|max:255',
            'near_landmark'   => 'nullable|string|max:255',
            'distance_to_office' => 'nullable|integer',
            'lat'             => 'required|numeric|between:-90,90',
            'lon'             => 'required|numeric|between:-180,180',
        ]);

        $station = PollingStation::create([
            'county'          => $request->county,
            'constituency'    => $request->constituency,
            'office'          => $request->office,
            'near_landmark'   => $request->near_landmark,
            'distance_to_office' => $request->distance_to_office ?? 0,
            'lat'             => $request->lat,
            'lon'             => $request->lon,
            'is_user_added'   => $request->user() ? true : false,   // mark if added from app
        ]);

        return response()->json([
            'message' => 'Polling station added successfully',
            'station' => $station
        ], 201);
    }

    /**
     * GET /api/stations/nearby - Get stations near user location
     */
    public function nearbyStations(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:100|max:100000',   // in meters, default 10km
        ]);

        $stations = PollingStation::nearby(
            $request->lat,
            $request->lon,
            $request->radius ?? 10000   // default 10km
        )->get();

        return response()->json($stations);
    }

    /**
 * Bulk Import Polling Stations from JSON
 */
public function importStations(Request $request)
{
    $request->validate([
        'stations' => 'required|array',
        'stations.*.county' => 'required|string',
        'stations.*.constituency' => 'required|string',
        'stations.*.office' => 'required|string',
        'stations.*.lat' => 'required|numeric',
        'stations.*.lon' => 'required|numeric',
    ]);

    $data = $request->stations;

    // Bulk insert (very fast)
    $imported = PollingStation::insert($data); // or use createMany() if you want events

    return response()->json([
        'message' => 'Import completed',
        'imported' => count($data)
    ]);
}

public function getAllStations()
{
    $stations = PollingStation::all();
    return response()->json($stations);
}

/**
 * GET /api/stations/by-county?county=Mombasa
 * Get all stations in a specific county
 */
public function getByCounty(Request $request)
{
    $request->validate([
        'county' => 'required|string|max:100',
    ]);

    $stations = PollingStation::where('county', 'LIKE', "%{$request->county}%")
        ->orderBy('constituency')
        ->orderBy('office')
        ->get();

    return response()->json($stations);
}

/**
     * 1. CREATE GROUP (Mobile API)
     * POST /api/groups/create
     */
    public function createGroup(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated. Please login first.'
            ], 401);
        }

        $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        // Generate unique 8-character invite code (e.g. "K7X9P2MQ")
        do {
            $inviteCode = strtoupper(Str::random(8));
        } while (Group::where('invite_code', $inviteCode)->exists());

        $group = Group::create([
            'name'        => $request->name,
            'description' => $request->description,
            'created_by'  => $user->id,
            'invite_code' => $inviteCode,
        ]);

        // Creator automatically becomes a member
        GroupMember::create([
            'group_id' => $group->id,
            'user_id'  => $user->id,
        ]);

        return response()->json([
            'message'     => 'Group created successfully',
            'group'       => [
                'id'          => $group->id,
                'name'        => $group->name,
                'description' => $group->description,
                'invite_code' => $group->invite_code,
                // You can build a deep link here if your Flutter app supports it
                'invite_link' => "yourapp://group/join?code={$group->invite_code}", // ← change "yourapp" to your scheme
            ]
        ], 201);
    }

    /**
     * 2. JOIN GROUP via invite code (Mobile API)
     * POST /api/groups/join
     */
    public function joinGroup(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated. Please login first.'
            ], 401);
        }

        $request->validate([
            'invite_code' => 'required|string|size:8',
        ]);

        $group = Group::where('invite_code', strtoupper($request->invite_code))->first();

        if (!$group) {
            return response()->json([
                'message' => 'Invalid or expired invite code'
            ], 404);
        }

        // Check if already a member
        $alreadyMember = GroupMember::where('group_id', $group->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyMember) {
            return response()->json([
                'message' => 'You are already a member of this group'
            ], 400);
        }

        GroupMember::create([
            'group_id' => $group->id,
            'user_id'  => $user->id,
        ]);

        return response()->json([
            'message' => 'Successfully joined group',
            'group'   => [
                'id'          => $group->id,
                'name'        => $group->name,
                'description' => $group->description,
            ]
        ], 200);
    }

    /**
     * 3. SEND MESSAGE in a group (Mobile API)
     * POST /api/groups/{group_id}/messages   or   POST /api/groups/send-message
     */
    public function sendGroupMessage(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated. Please login first.'
            ], 401);
        }

        $request->validate([
            'group_id'  => 'required|exists:groups,id',
            'message'   => 'required|string|max:500',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // Verify user is a member of the group
        $isMember = GroupMember::where('group_id', $request->group_id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isMember) {
            return response()->json([
                'message' => 'You are not a member of this group'
            ], 403);
        }

        $groupMessage = GroupMessage::create([
            'group_id'  => $request->group_id,
            'username'  => $user->username ?? $user->name ?? 'Anonymous',
            'message'   => $request->message,
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json([
            'message' => 'Message sent to group successfully',
            'sent'    => $groupMessage->only(['id', 'username', 'message', 'created_at'])
        ], 201);
    }

    /**
 * GET /api/groups/{group_id}/messages
 */
public function getGroupMessages(Request $request, $group_id)
{
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthenticated. Please login first.'
        ], 401);
    }

    // Validate that group exists
    $group = Group::find($group_id);
    if (!$group) {
        return response()->json([
            'message' => 'Group not found'
        ], 404);
    }

    // Verify user is a member
    $isMember = GroupMember::where('group_id', $group_id)
        ->where('user_id', $user->id)
        ->exists();

    if (!$isMember) {
        return response()->json([
            'message' => 'You are not a member of this group'
        ], 403);
    }

    $messages = GroupMessage::where('group_id', $group_id)
        ->orderBy('created_at', 'asc')
        ->get(['username', 'message', 'latitude', 'longitude', 'created_at']);

    return response()->json($messages);
}
    
    public function getMyGroups(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $groups = GroupMember::where('user_id', $user->id)
            ->with(['group' => function ($query) {
                $query->select('id', 'name', 'description', 'invite_code', 'created_at');
            }])
            ->get()
            ->pluck('group');

        return response()->json($groups);
    }

    /**
 * Show web form to send public message
 */
public function createMessageForm()
{
    $counties = [ /* your county list */ ];
    return view('messages.create', compact('counties'));
}

/**
 * Store message from web form
 */
public function storeMessageFromWeb(Request $request)
{
    $request->validate([
        'message'      => 'required|string|max:500',
        'county'       => 'required|string|max:100',
        'constituency' => 'required|string|max:100',
        'latitude'     => 'nullable|numeric',
        'longitude'    => 'nullable|numeric',
    ]);

    Message::create([
        'username'     => auth()->user()->username ?? auth()->user()->name ?? 'Web User',
        'message'      => $request->message,
        'county'       => $request->county,
        'constituency' => $request->constituency,
        // 'latitude'     => $request->latitude,
        // 'longitude'    => $request->longitude,
        'latitude'          => $request->latitude ?? 0,      // ← Temporary default
            'longitude'         => $request->longitude ?? 0, 
    ]);

    return redirect()->route('dashboard.messages')
                     ->with('success', 'Message sent successfully!');
}

    /**
     * Unified method to send message to any location level
     * POST /api/send-location-message
     */
    // public function sendLocationMessage(Request $request)
    // {
    //     $user = $request->user();

    //     if (!$user) {
    //         return response()->json(['message' => 'Unauthenticated'], 401);
    //     }

    //     $request->validate([
    //         'message'            => 'required|string|max:500',
    //         'level'              => 'required|in:country,county,constituency,ward',
    //         'name'               => 'required|string|max:100',   // country/county/etc name
    //         'quoted_message_id'  => 'nullable|exists:messages,id',
    //         'latitude'           => 'nullable|numeric|between:-90,90',
    //         'longitude'          => 'nullable|numeric|between:-180,180',
    //     ]);

    //     $message = Message::create([
    //         'username'          => $user->username ?? $user->name ?? 'Anonymous',
    //         'message'           => $request->message,
    //         'country'           => $request->level === 'country' ? $request->name : null,
    //         'county'            => $request->level === 'county' ? $request->name : null,
    //         'constituency'      => $request->level === 'constituency' ? $request->name : null,
    //         'ward'              => $request->level === 'ward' ? $request->name : null,
    //         'quoted_message_id' => $request->quoted_message_id,
    //         'latitude'          => $request->latitude,
    //         'longitude'         => $request->longitude,
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Message sent successfully',
    //         'data'    => $message->load('quotedMessage')
    //     ], 201);
    // }

    // /**
    //  * Get messages for any location level
    //  * GET /api/location-messages?level=county&name=Kiambu
    //  */
    // public function getLocationMessages(Request $request)
    // {
    //     $request->validate([
    //         'level' => 'required|in:country,county,constituency,ward',
    //         'name'  => 'required|string|max:100',
    //     ]);

    //     $messages = Message::inLocation($request->level, $request->name)
    //         ->with('quotedMessage')
    //         ->latest()
    //         ->take(100)
    //         ->get([
    //             'id', 'username', 'message', 'quoted_message_id',
    //             'latitude', 'longitude', 'created_at'
    //         ]);

    //     return response()->json([
    //         'success' => true,
    //         'messages' => $messages
    //     ]);
    // }

        /**
     * Send message to any location level (Country, County, Constituency, Ward)
     * POST /api/send-location-message
     */
    public function sendLocationMessage(Request $request)
{
    $user = $request->user();

    if (!$user) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    $request->validate([
        'message'           => 'required|string|max:500',
        'level'             => 'required|in:country,county,constituency,ward',
        'name'              => 'required|string|max:100',
        'quoted_message_id' => 'nullable|exists:messages,id',
        'tag_id'            => 'nullable|exists:tags,id',
        'latitude'          => 'nullable|numeric|between:-90,90',
        'longitude'         => 'nullable|numeric|between:-180,180',
    ]);

    $data = [
        'username'          => $user->username ?? $user->name ?? 'Anonymous',
        'message'           => $request->message,
        'quoted_message_id' => $request->quoted_message_id,
        'tag_id'            => $request->tag_id,
        'latitude'          => $request->latitude,
        'longitude'         => $request->longitude,
    ];

    // Set correct location field
    switch ($request->level) {
        case 'country':      $data['country'] = $request->name; break;
        case 'county':       $data['county'] = $request->name; break;
        case 'constituency': $data['constituency'] = $request->name; break;
        case 'ward':         $data['ward'] = $request->name; break;
    }

    $message = Message::create($data);

    return response()->json([
        'success' => true,
        'message' => 'Message sent successfully',
        'data'    => $message->load(['quotedMessage', 'tag'])
    ], 201);
}

    // public function sendLocationMessage(Request $request)
    // {
    //     $user = $request->user();

    //     if (!$user) {
    //         return response()->json(['message' => 'Unauthenticated'], 401);
    //     }

    //     $request->validate([
    //         'message'            => 'required|string|max:500',
    //         'level'              => 'required|in:country,county,constituency,ward',
    //         'name'               => 'required|string|max:100',
    //         'quoted_message_id'  => 'nullable|exists:messages,id',
    //         'latitude'           => 'nullable|numeric|between:-90,90',
    //         'longitude'          => 'nullable|numeric|between:-180,180',
    //     ]);

    //     $data = [
    //         'username'          => $user->username ?? $user->name ?? 'Anonymous',
    //         'message'           => $request->message,
    //         'quoted_message_id' => $request->quoted_message_id,
    //         'latitude'          => $request->latitude,
    //         'longitude'         => $request->longitude,
    //     ];

    //     // Set only the relevant location field
    //     switch ($request->level) {
    //         case 'country':
    //             $data['country'] = $request->name;
    //             break;
    //         case 'county':
    //             $data['county'] = $request->name;
    //             break;
    //         case 'constituency':
    //             $data['constituency'] = $request->name;
    //             break;
    //         case 'ward':
    //             $data['ward'] = $request->name;
    //             break;
    //     }

    //     $message = Message::create($data);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Message sent successfully',
    //         'data'    => $message->load('quotedMessage')
    //     ], 201);
    // }

        /**
     * Get messages for any location level
     * GET /api/location-messages?level=county&name=Kiambu
     */
    public function getLocationMessages(Request $request)
    {
        $request->validate([
            'level' => 'required|in:country,county,constituency,ward',
            'name'  => 'required|string|max:100',
        ]);

        $messages = Message::inLocation($request->level, $request->name)
            // ->with('quotedMessage')
            ->with(['quotedMessage', 'tag', 'reactions'])
            ->latest()
            ->take(100)
            ->get([
                'id', 'username', 'message', 'quoted_message_id',
                'country', 'county', 'constituency', 'ward',
                'latitude', 'longitude', 'created_at', 'tag_id',
            ]);

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    public function getTags()
{
    $tags = \App\Models\Tag::select('id', 'name', 'slug')
                ->orderBy('name')
                ->get();

    return response()->json([
        'success' => true,
        'tags'    => $tags
    ]);
}

    /**
     * React to a message
     * POST /api/messages/{message_id}/react
     */

        /**
     * React to a message (POST /api/messages/{message_id}/react)
     */
    public function reactToMessage(Request $request, $message_id)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'reaction' => 'required|string|max:10',
        ]);

        // Find the message
        $message = Message::findOrFail($message_id);

        // Remove previous reaction by this user (optional - prevents duplicate reactions)
        MessageReaction::where('message_id', $message_id)
            ->where('user_id', $user->id)
            ->delete();

        // Create new reaction
        $reaction = MessageReaction::create([
            'message_id' => $message_id,
            'user_id'    => $user->id,
            'reaction'   => $request->reaction,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reaction added successfully',
            'reaction' => $reaction
        ], 201);
    }

    // public function reactToMessage(Request $request, $message_id)
    // {
    //     $user = $request->user();

    //     if (!$user) {
    //         return response()->json(['message' => 'Unauthenticated'], 401);
    //     }

    //     $request->validate([
    //         'reaction' => 'required|string|max:10', // 👍 ❤️ 🔥 etc.
    //     ]);

    //     $message = Message::findOrFail($message_id);

    //     // Remove previous reaction by this user
    //     MessageReaction::where('message_id', $message_id)
    //         ->where('user_id', $user->id)
    //         ->delete();

    //     // Add new reaction
    //     $reaction = MessageReaction::create([
    //         'message_id' => $message_id,
    //         'user_id'    => $user->id,
    //         'reaction'   => $request->reaction,
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Reaction added',
    //         'reaction' => $reaction
    //     ]);
    // }
}