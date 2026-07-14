<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddStationRequest;
use App\Http\Requests\Api\CreateGroupRequest;
use App\Http\Requests\Api\GetByCountyRequest;
use App\Http\Requests\Api\GetConstituencyMessagesRequest;
use App\Http\Requests\Api\GetLocationMessagesRequest;
use App\Http\Requests\Api\ImportStationsRequest;
use App\Http\Requests\Api\JoinGroupRequest;
use App\Http\Requests\Api\NearbyMessagesRequest;
use App\Http\Requests\Api\NearbyStationsRequest;
use App\Http\Requests\Api\ReactToMessageRequest;
use App\Http\Requests\Api\SendGroupMessageRequest;
use App\Http\Requests\Api\SendLocationMessageRequest;
use App\Http\Requests\Api\SendMessageRequest;
use App\Http\Requests\Api\StoreMessageFromWebRequest;
use App\Http\Requests\Api\UpdateVoterStatusRequest;
use App\Http\Requests\Api\RespondToPollRequest;
use App\Models\AspirantPoll;
use App\Services\Api\GroupService;
use App\Services\Api\MessageService;
use App\Services\Api\PollingStationService;
use App\Services\Api\VoterService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        private MessageService $messageService,
        private GroupService $groupService,
        private PollingStationService $pollingStationService,
        private VoterService $voterService
    ) {}

    public function index()
    {
        $data = $this->messageService->getLatestMessages(50);

        return view('messages.index', $data);
    }

    public function getCounties()
    {
        $counties = config('kenya.counties');

        return response()->json($counties);
    }

    public function getConstituencies(Request $request)
    {
        $county = trim($request->query('county'));
        $data = $this->messageService->getConstituencies($county);

        return response()->json($data[$county] ?? []);
    }

    public function sendMessage(SendMessageRequest $request)
    {
        try {
            $result = $this->messageService->sendMessage($request->user(), $request->validated());

            return response()->json($result, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send message', 'error' => $e->getMessage()], 500);
        }
    }

    public function getConstituencyMessages(GetConstituencyMessagesRequest $request)
    {
        try {
            $messages = $this->messageService->getConstituencyMessages($request->county, $request->constituency);

            return response()->json($messages);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve messages', 'error' => $e->getMessage()], 500);
        }
    }

    public function nearbyMessages(NearbyMessagesRequest $request)
    {
        try {
            $messages = $this->messageService->getNearbyMessages($request->latitude, $request->longitude);

            return response()->json($messages);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve nearby messages', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateVoterStatus(UpdateVoterStatusRequest $request)
    {
        try {
            $result = $this->voterService->updateVoterStatus($request->user(), $request->validated());

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update voter status', 'error' => $e->getMessage()], 500);
        }
    }

    public function getVoterStatus(Request $request)
    {
        try {
            $status = $this->voterService->getVoterStatus($request->user());

            return response()->json($status);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve voter status', 'error' => $e->getMessage()], 500);
        }
    }

    public function getAllVoters()
    {
        try {
            $voters = $this->voterService->getAllVoters();

            return response()->json($voters);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve voters', 'error' => $e->getMessage()], 500);
        }
    }

    public function addStation(AddStationRequest $request)
    {
        try {
            $result = $this->pollingStationService->addStation($request->validated(), $request->user());

            return response()->json($result, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to add polling station', 'error' => $e->getMessage()], 500);
        }
    }

    public function nearbyStations(NearbyStationsRequest $request)
    {
        try {
            $stations = $this->pollingStationService->getNearbyStations($request->lat, $request->lon, $request->radius);

            return response()->json($stations);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve nearby stations', 'error' => $e->getMessage()], 500);
        }
    }

    public function importStations(ImportStationsRequest $request)
    {
        try {
            $result = $this->pollingStationService->importStations($request->stations);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to import stations', 'error' => $e->getMessage()], 500);
        }
    }

    public function getAllStations()
    {
        try {
            $stations = $this->pollingStationService->getAllStations();

            return response()->json($stations);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve stations', 'error' => $e->getMessage()], 500);
        }
    }

    public function getByCounty(GetByCountyRequest $request)
    {
        try {
            $stations = $this->pollingStationService->getByCounty($request->county);

            return response()->json($stations);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve stations by county', 'error' => $e->getMessage()], 500);
        }
    }

    public function createGroup(CreateGroupRequest $request)
    {
        try {
            $result = $this->groupService->createGroup($request->user(), $request->validated());

            return response()->json($result, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create group', 'error' => $e->getMessage()], 500);
        }
    }

    public function joinGroup(JoinGroupRequest $request)
    {
        try {
            $result = $this->groupService->joinGroup($request->user(), $request->invite_code);

            return response()->json($result, 200);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;

            return response()->json(['message' => $e->getMessage()], $statusCode);
        }
    }

    public function sendGroupMessage(SendGroupMessageRequest $request)
    {
        try {
            $result = $this->groupService->sendGroupMessage($request->user(), $request->validated());

            return response()->json($result, 201);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;

            return response()->json(['message' => $e->getMessage()], $statusCode);
        }
    }

    public function getGroupMessages(Request $request, $group_id)
    {
        try {
            $messages = $this->groupService->getGroupMessages($request->user(), $group_id);

            return response()->json($messages);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;

            return response()->json(['message' => $e->getMessage()], $statusCode);
        }
    }

    public function respondToPoll(RespondToPollRequest $request, AspirantPoll $poll)
    {
        try {
            $result = $this->groupService->respondToPoll(
                $request->user(),
                $poll,
                (int) $request->validated('option_index')
            );

            return response()->json($result, 200);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;

            return response()->json(['message' => $e->getMessage()], $statusCode);
        }
    }
    public function getMyGroups(Request $request)
    {
        try {
            $groups = $this->groupService->getUserGroups($request->user());

            return response()->json($groups);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve groups', 'error' => $e->getMessage()], 500);
        }
    }

    public function createMessageForm()
    {
        $counties = $this->messageService->getCounties();

        return view('messages.create', compact('counties'));
    }

    public function storeMessageFromWeb(StoreMessageFromWebRequest $request)
    {
        try {
            $this->messageService->storeMessageFromWeb(auth()->user(), $request->validated());

            return redirect()->route('dashboard.messages')->with('success', 'Message sent successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send message: ' . $e->getMessage());
        }
    }

    public function sendLocationMessage(SendLocationMessageRequest $request)
    {
        try {
            $result = $this->messageService->sendLocationMessage($request->user(), $request->validated());

            return response()->json($result, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send message', 'error' => $e->getMessage()], 500);
        }
    }

    public function getLocationMessages(GetLocationMessagesRequest $request)
    {
        try {
            $result = $this->messageService->getLocationMessages($request->level, $request->name);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve messages', 'error' => $e->getMessage()], 500);
        }
    }

    public function getTags()
    {
        try {
            $result = $this->messageService->getTags();

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve tags', 'error' => $e->getMessage()], 500);
        }
    }

    public function reactToMessage(ReactToMessageRequest $request, $message_id)
    {
        try {
            $result = $this->messageService->reactToMessage($request->user(), $message_id, $request->reaction);

            return response()->json($result, 201);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;

            return response()->json(['message' => $e->getMessage()], $statusCode);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->messageService->deleteMessage($request->user(), $id);

            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully.'
            ]);
        } catch (\Exception $e) {
            $statusCode = $e->getCode() ?: 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}

