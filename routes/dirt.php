
///////


// <?php

// use App\Http\Controllers\ProfileController;
// use App\Http\Controllers\LocationController;
// use App\Http\Controllers\DashboardController;
// use App\Http\Controllers\MessageController;
// use App\Http\Controllers\GroupController;
// use Illuminate\Support\Facades\Route;

// // Public route
// Route::get('/', function () {
//     return view('welcome');
// });

// // Public Landing Page
// Route::get('/', [App\Http\Controllers\LandingController::class, 'index'])->name('landing');

// Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
//     Route::resource('blocs', App\Http\Controllers\Admin\BlocController::class);
//     Route::resource('counties', App\Http\Controllers\Admin\CountyController::class);
//     Route::resource('constituencies', App\Http\Controllers\Admin\ConstituencyController::class);
//     Route::resource('wards', App\Http\Controllers\Admin\WardController::class);

//     // Import Routes
//     Route::post('blocs/import', [App\Http\Controllers\Admin\BlocController::class, 'import'])->name('blocs.import');
//     Route::post('counties/import', [App\Http\Controllers\Admin\CountyController::class, 'import'])->name('counties.import');
//     Route::post('constituencies/import', [App\Http\Controllers\Admin\ConstituencyController::class, 'import'])->name('constituencies.import');
//     Route::post('wards/import', [App\Http\Controllers\Admin\WardController::class, 'import'])->name('wards.import');
// });

// // ====================== AUTHENTICATED ROUTES ======================
// Route::middleware('auth')->group(function () {

//     Route::resource('blocs', App\Http\Controllers\Admin\BlocController::class);
//     Route::resource('counties', App\Http\Controllers\Admin\CountyController::class);
//     Route::resource('constituencies', App\Http\Controllers\Admin\ConstituencyController::class);
//     Route::resource('wards', App\Http\Controllers\Admin\WardController::class);
//     // Dashboard Home
//     Route::get('/dashboard', [DashboardController::class, 'index'])
//         ->name('dashboard');

//     Route::get('/dashboard/stats', [DashboardController::class, 'stats'])
//      ->name('dashboard.stats');
     

// Route::get('/dashboard/stations', [DashboardController::class, 'stations'])
//      ->name('dashboard.stations');
//     // Messages Dashboard (Main page with Counties + Groups tabs)
//     Route::get('/dashboard/messages', [DashboardController::class, 'messages'])
//         ->name('dashboard.messages');

//     // Public Messages (Web form - optional)
//     Route::get('/messages/create', [MessageController::class, 'createMessageForm'])
//         ->name('messages.create');

//     Route::post('/messages', [MessageController::class, 'storeMessageFromWeb'])
//         ->name('messages.store');

//     // Group Routes
//     Route::get('/groups/create', [GroupController::class, 'create'])
//         ->name('groups.create');

//     Route::post('/groups', [GroupController::class, 'store'])
//         ->name('groups.store');

//     Route::get('/groups/{group}', [GroupController::class, 'show'])
//         ->name('groups.show');

//     Route::post('/groups/{group}/messages', [GroupController::class, 'sendMessage'])
//         ->name('groups.messages.store');

//     // Other admin routes
//     Route::get('/locations', [LocationController::class, 'adminIndex'])
//         ->name('locations.index');

//     Route::get('/users', [LocationController::class, 'adminUsers'])
//         ->name('users.index');

//     // Profile (Breeze)
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

//     // Stations Import
//     Route::post('/stations/import', [DashboardController::class, 'importStations'])
//         ->name('stations.import');
// });

// Route::get('/privacy', function () {
//     return view('privacy');
// })->name('privacy');

// // Route::get('/privacy', function () {
// //     return view('privacy');        // create resources/views/privacy.blade.php later
// // })->name('privacy');

// // API-like route for messages index (if needed)
// Route::get('/messages', [MessageController::class, 'index'])
//     ->name('messages.index')
//     ->middleware('auth');

// require __DIR__.'/auth.php';

// <!-- <?php

// use App\Http\Controllers\ProfileController;
// use App\Http\Controllers\LocationController;
// use App\Http\Controllers\DashboardController;
// use Illuminate\Support\Facades\Route;

// // Public route
// Route::get('/', function () {
//     return view('welcome');
// });

// // Authenticated routes
// Route::middleware('auth')->group(function () {

//     Route::middleware('auth')->group(function () {

//     Route::get('/dashboard', [DashboardController::class, 'index'])
//         ->name('dashboard');

//     Route::get('/locations', [LocationController::class, 'adminIndex'])
//         ->name('locations.index');

//     Route::get('/users', [LocationController::class, 'adminUsers'])
//         ->name('users.index');
// });
//     // Locations
//     Route::get('/locations', [LocationController::class, 'adminIndex'])
//         ->name('locations.index');

//     // Users
//     Route::get('/users', [LocationController::class, 'adminUsers'])
//         ->name('users.index');

//     // Profile routes (from Breeze)
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

// Group Routes - Web (Backend Admin/Staff View)
// Route::middleware('auth')->group(function () {
//     // Inside your auth middleware group
// Route::get('/messages/create', [MessageController::class, 'createMessageForm'])
//      ->name('messages.create');

// Route::post('/messages', [MessageController::class, 'storeMessageFromWeb'])
//      ->name('messages.store');
//     // Create Group Form
//     Route::get('/groups/create', [GroupController::class, 'create'])
//          ->name('groups.create');
    
//     // Store New Group
//     Route::post('/groups', [GroupController::class, 'store'])
//          ->name('groups.store');
    
//     // Show Single Group Chat Room
//     Route::get('/groups/{group}', [GroupController::class, 'show'])
//          ->name('groups.show');
    
    // (Optional) Send message from web (if you want web support)
//     Route::post('/groups/{group}/messages', [GroupController::class, 'sendMessage'])
//          ->name('groups.messages.store');
// });
// Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
// Route::middleware('auth')->group(function () {
//     Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
//     // New dedicated routes for tabs
//     Route::get('/dashboard/messages', [DashboardController::class, 'messages'])->name('dashboard.messages');
//     Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
//     Route::get('/dashboard/stations', [DashboardController::class, 'stations'])->name('dashboard.stations');
    

// // Route::post('/stations/import', [DashboardController::class, 'importStations'])->name('stations.import');
//     // Existing
//     Route::get('/users', [LocationController::class, 'adminUsers'])->name('users.index');
//     Route::get('/locations', [LocationController::class, 'adminIndex'])->name('locations.index');
// });
// Route::post('/stations/import', [DashboardController::class, 'importStations'])
//          ->name('stations.import');

// require __DIR__.'/auth.php'; -->