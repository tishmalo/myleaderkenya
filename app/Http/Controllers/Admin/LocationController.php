<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\LocationService;
use App\Services\Admin\UserService;
use App\Services\Admin\DashboardService;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function __construct(
        private LocationService $locationService,
        private UserService $userService,
        private DashboardService $dashboardService
    ) {}

    // ====================== ADMIN WEB VIEWS ======================

    /**
     * GET /locations  (Admin dashboard – raw locations list)
     */
    public function adminIndex()
    {
        $locations = $this->locationService->getAllLocations();

        return view('locations.index', compact('locations'));
    }

    /**
     * GET /users  (Admin dashboard – registered users with locations)
     */
    public function adminUsers()
    {
        $users = $this->userService->getUsersWithLocationPaginated(20);

        return view('users.index', compact('users'));
    }

    /**
     * GET /dashboard  (Legacy dashboard view)
     */
    public function dashboard()
    {
        $users     = $this->userService->getUsersWithLocation();
        $stats     = $this->dashboardService->getDashboardStats();
        $locations = $this->locationService->getAllLocations();

        return view('dashboard', [
            'users'           => $users,
            'messages'        => $stats['messages'],
            'totalVoters'     => $stats['totalVoters'],
            'confirmedVoters' => $stats['voterStats']['confirmedVoters'],
            'avgAge'          => $stats['voterStats']['avgAge'],
            'locations'       => $locations
        ]);
    }
}
