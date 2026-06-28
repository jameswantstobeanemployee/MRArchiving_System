<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StorageController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\AiHealthController;

// Auth
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware(['auth', 'active.user'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Patients ─────────────────────────────────────────────────────────────
    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/search/api',     [PatientController::class, 'search'])->name('search');
        Route::get('/',               [PatientController::class, 'index'])->name('index');
        Route::get('/create',         [PatientController::class, 'create'])->name('create');
        Route::post('/',              [PatientController::class, 'store'])->name('store');
        Route::get('/{patient}',      [PatientController::class, 'show'])->name('show');
        Route::get('/{patient}/edit', [PatientController::class, 'edit'])->name('edit');
        Route::put('/{patient}',      [PatientController::class, 'update'])->name('update');
    });

    // ── Profile ───────────────────────────────────────────────────────────────
    Route::get('/profile',                  [ProfileController::class, 'index'])             ->name('profile.index');
    Route::patch('/profile',                [ProfileController::class, 'update'])            ->name('profile.update');
    Route::patch('/profile/password',       [ProfileController::class, 'updatePassword'])    ->name('profile.password');
    Route::delete('/profile/sessions/{id}', [ProfileController::class, 'revokeSession'])     ->name('profile.sessions.revoke')->where('id', '.*');
    Route::match(['POST', 'DELETE'], '/profile/sessions', [ProfileController::class, 'logoutOtherSessions'])
        ->name('profile.logout-other-sessions');

    // ── Charts ────────────────────────────────────────────────────────────────
        // ── Charts ────────────────────────────────────────────────────────────────
    Route::prefix('charts')->name('charts.')->group(function () {

        // ── AJAX / API helpers ───────────────────────────────────────────────
        Route::get('/box/{box}/info',    [ChartController::class, 'getBoxInfo'])->name('box.info');

        // ── Chunked upload ───────────────────────────────────────────────────
        Route::post('/upload/chunk',     [ChartController::class, 'uploadChunk'])->name('upload.chunk');

        // ── Progress polling ─────────────────────────────────────────────────
        Route::get('/progress/{jobId}',  [ChartController::class, 'progress'])->name('progress');

        // ── Orphaned charts ──────────────────────────────────────────────────
        Route::get('/orphaned',          [ChartController::class, 'orphanedCharts'])->name('orphaned');
        Route::post('/orphaned/assign',  [ChartController::class, 'bulkAssignOrphaned'])->name('orphaned.assign');

        // ── Admin-only compression routes (before /{chart} wildcard) ─────────
        Route::middleware('admin')->group(function () {
            Route::get('/failed-compressions',
                [ChartController::class, 'failedCompressions']
            )->name('failed-compressions');

            Route::post('/retry-compression/all',
                [ChartController::class, 'retryCompressionAll']
            )->name('retry-compression-all');

            Route::post('/retry-compression/bulk',
                [ChartController::class, 'retryCompressionBulk']
            )->name('retry-compression-bulk');
        });

        // ── Standard CRUD ────────────────────────────────────────────────────
        Route::get('/',                  [ChartController::class, 'index'])->name('index');
        Route::get('/create',            [ChartController::class, 'create'])->name('create');
        Route::post('/',                 [ChartController::class, 'store'])->name('store');

        // ── Wildcard /{chart} routes LAST ────────────────────────────────────
        Route::get('/{chart}',           [ChartController::class, 'show'])->name('show');
        Route::get('/{chart}/move',      [ChartController::class, 'move'])->name('move');
        Route::post('/{chart}/move',     [ChartController::class, 'move'])->name('move.store');
        Route::delete('/{chart}',        [ChartController::class, 'destroy'])->name('destroy');
        Route::get('/{chart}/download',  [ChartController::class, 'download'])->name('download');

        // retry-compression uses /{chart} so must also be after static routes
        // but still needs admin middleware
        Route::middleware('admin')->group(function () {
            Route::post('/{chart}/retry-compression',
                [ChartController::class, 'retryCompression']
            )->name('retry-compression');
        });
    });

    // ── Checkout ──────────────────────────────────────────────────────────────
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/select',                 [CheckoutController::class, 'selectChart'])->name('select');
        Route::get('/chart/{chart}/create',   [CheckoutController::class, 'create'])->name('create');
        Route::post('/chart/{chart}',         [CheckoutController::class, 'store'])->name('store');
        Route::post('/chart/{chart}/checkin', [CheckoutController::class, 'checkin'])->name('checkin');
        Route::get('/',                       [CheckoutController::class, 'index'])->name('index');
        Route::get('/{checkout}',             [CheckoutController::class, 'show'])->name('show');
    });

    // ── Locations ─────────────────────────────────────────────────────────────
    Route::prefix('locations')->name('locations.')->group(function () {
        // AJAX helpers
        Route::get('/api/rooms/{room}/shelves',  [LocationController::class, 'getShelvesByRoom'])->name('api.shelves');
        Route::get('/api/shelves/{shelf}/boxes', [LocationController::class, 'getBoxesByShelf'])->name('api.boxes');

        // Rooms
        Route::prefix('rooms')->name('rooms.')->group(function () {
            Route::get('/',              [LocationController::class, 'roomsIndex'])->name('index');
            Route::get('/create',        [LocationController::class, 'roomCreate'])->name('create');
            Route::post('/',             [LocationController::class, 'roomStore'])->name('store');
            Route::get('/{room}',        [LocationController::class, 'roomShow'])->name('show');
            Route::get('/{room}/edit',   [LocationController::class, 'roomEdit'])->name('edit');
            Route::put('/{room}',        [LocationController::class, 'roomUpdate'])->name('update');
            Route::delete('/{room}',     [LocationController::class, 'roomDestroy'])->name('destroy');
        });

        // Shelves
        Route::prefix('rooms/{room}/shelves')->name('shelves.')->group(function () {
            Route::get('/create',        [LocationController::class, 'shelfCreate'])->name('create');
            Route::post('/',             [LocationController::class, 'shelfStore'])->name('store');
            Route::get('/{shelf}/edit',  [LocationController::class, 'shelfEdit'])->name('edit');
            Route::put('/{shelf}',       [LocationController::class, 'shelfUpdate'])->name('update');
            Route::delete('/{shelf}',    [LocationController::class, 'shelfDestroy'])->name('destroy');
        });

        // Boxes — create/store (needs shelf context)
        Route::prefix('shelves/{shelf}/boxes')->name('boxes.')->group(function () {
            Route::get('/create', [LocationController::class, 'boxCreate'])->name('create');
            Route::post('/',      [LocationController::class, 'boxStore'])->name('store');
        });

        // Boxes — show/edit/update/delete (box ID is globally unique)
        Route::prefix('boxes')->name('boxes.')->group(function () {
            Route::get('/{box}',      [LocationController::class, 'boxShow'])->name('show');
            Route::get('/{box}/edit', [LocationController::class, 'boxEdit'])->name('edit');
            Route::put('/{box}',      [LocationController::class, 'boxUpdate'])->name('update');
            Route::delete('/{box}',   [LocationController::class, 'boxDestroy'])->name('destroy');
        });
    });

    // ── Notifications ─────────────────────────────────────────────────────────
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/api/unread-count',      [NotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::post('/read-all',             [NotificationController::class, 'markAllRead'])->name('read-all');
        Route::get('/',                      [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/read',  [NotificationController::class, 'markRead'])->name('read');
        Route::delete('/{notification}',     [NotificationController::class, 'destroy'])->name('destroy');
    });

    // ── Reports ───────────────────────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/',                  [ReportController::class, 'index'])->name('index');
        Route::get('/archive-inventory', [ReportController::class, 'archiveInventory'])->name('archive-inventory');
        Route::get('/box-status',        [ReportController::class, 'boxStatus'])->name('box-status');
        Route::get('/checkout-status',   [ReportController::class, 'checkoutStatus'])->name('checkout-status');
        Route::get('/location-history',  [ReportController::class, 'locationHistory'])->name('location-history');
        Route::get('/retention',         [ReportController::class, 'retentionReport'])->name('retention');
        Route::get('/storage-usage',     [ReportController::class, 'storageUsage'])->name('storage-usage');
        Route::get('/activity',          [ReportController::class, 'activityReport'])->name('activity');
        Route::get('/audit-trail',       [ReportController::class, 'auditTrail'])->name('audit-trail');
    });

    // ── Global search ─────────────────────────────────────────────────────────
    Route::get('/api/global-search',             [\App\Http\Controllers\GlobalSearchController::class, 'search'])->name('api.global-search');
    Route::get('/api/global-search/autocomplete',[\App\Http\Controllers\GlobalSearchController::class, 'autocomplete'])->name('api.global-search.autocomplete');
    Route::get('/api/compression-queue',         [ChartController::class, 'compressionQueue'])->name('api.compression-queue');
    // ── Preferences ───────────────────────────────────────────────────────────
    Route::get('/preferences',  [SettingsController::class, 'preferences'])->name('preferences');
    Route::post('/preferences', [SettingsController::class, 'updatePreferences'])->name('preferences.update');

    // ── Admin ─────────────────────────────────────────────────────────────────
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/',                     [UserController::class, 'index'])->name('index');
            Route::get('/create',               [UserController::class, 'create'])->name('create');
            Route::post('/',                    [UserController::class, 'store'])->name('store');
            Route::get('/{user}/edit',          [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}',               [UserController::class, 'update'])->name('update');
            Route::get('/{user}/login-history', [UserController::class, 'loginHistory'])->name('login-history');
        });

        Route::prefix('storage')->name('storage.')->group(function () {
            Route::get('/',                     [StorageController::class, 'index'])->name('index');
            Route::get('/create',               [StorageController::class, 'create'])->name('create');
            Route::post('/',                    [StorageController::class, 'store'])->name('store');
            Route::get('/{drive}/edit',         [StorageController::class, 'edit'])->name('edit');
            Route::put('/{drive}',              [StorageController::class, 'update'])->name('update');
            Route::post('/{drive}/scan',        [StorageController::class, 'scan'])->name('scan');
            Route::post('/{drive}/set-primary', [StorageController::class, 'setPrimary'])->name('set-primary');
            Route::delete('/{drive}',           [StorageController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('backup')->name('backup.')->group(function () {
            Route::get('/',              [BackupController::class, 'index'])->name('index');
            Route::get('/create',        [BackupController::class, 'create'])->name('create');
            Route::post('/',             [BackupController::class, 'store'])->name('store');
            Route::get('/{backup}/edit', [BackupController::class, 'edit'])->name('edit');
            Route::put('/{backup}',      [BackupController::class, 'update'])->name('update');
            Route::delete('/{backup}',   [BackupController::class, 'destroy'])->name('destroy');
            Route::post('/{backup}/run', [BackupController::class, 'runNow'])->name('run');
            Route::get('/{backup}/logs', [BackupController::class, 'logs'])->name('logs');
        });

        Route::get('/settings',  [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::prefix('scanner')->name('scanner.')->group(function () {
            Route::get('/',                [\App\Http\Controllers\Admin\DriveScannerController::class, 'index'])->name('index');
            Route::post('/{drive}/scan',   [\App\Http\Controllers\Admin\DriveScannerController::class, 'scan'])->name('scan');
             Route::get('/{drive}/result',  [\App\Http\Controllers\Admin\DriveScannerController::class, 'result'])->name('result'); 
            Route::get('/{drive}/search',  [\App\Http\Controllers\Admin\DriveScannerController::class, 'search'])->name('search');
            Route::get('/{drive}/download',[\App\Http\Controllers\Admin\DriveScannerController::class, 'download'])->name('download');
            Route::post('/fix-path',       [\App\Http\Controllers\Admin\DriveScannerController::class, 'fixPath'])->name('fix-path');
            Route::post('/clear-path',     [\App\Http\Controllers\Admin\DriveScannerController::class, 'clearPath'])->name('clear-path');
            Route::post('/delete-orphan',  [\App\Http\Controllers\Admin\DriveScannerController::class, 'deleteOrphan'])->name('delete-orphan');
        });

        Route::prefix('ai-health')->name('ai-health.')->group(function () {
            Route::get('/',         [AiHealthController::class, 'index'])->name('index');
            Route::post('/scan',    [AiHealthController::class, 'scan'])->name('scan');
            Route::get('/{scanId}', [AiHealthController::class, 'show'])->name('show');
        });

        
        
    });
});