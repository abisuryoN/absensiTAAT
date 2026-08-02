<?php

use App\Http\Controllers\Api\StudentSessionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These routes are stateless and use Sanctum token authentication.
| Prefix: /api
*/

Route::middleware('auth:sanctum')->group(function () {

    // ── Student session info (for mobile apps) ────────────────────────────────
    // GET /api/student/session
    // Returns the resolved school session for the authenticated student,
    // including current time, gate windows, and status.
    Route::get('/student/session', [StudentSessionController::class, 'show']);

});
