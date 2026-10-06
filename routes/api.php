<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Branch\BranchController;
use App\Http\Controllers\Branch\BranchRequestController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Faculty\FacultyProfileController;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Upadhyay API  —  base path: /api/v1
|--------------------------------------------------------------------------
| Part 1: foundation (health)
| Part 2: auth, faculty profile, company, search, branch workflow   <- this file
| Part 3: jobs, applications, invitations, notifications, files, admin
*/

Route::prefix('v1')->middleware('throttle:api')->group(function () {

    Route::get('/health', fn () => ApiResponse::success([
        'app' => config('app.name'),
        'time' => now()->toIso8601String(),
    ], 'API is running'));

    // ---- Public auth -------------------------------------------------------
    Route::prefix('auth')->middleware('throttle:auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [PasswordController::class, 'forgot']);
        Route::post('/reset-password', [PasswordController::class, 'reset']);
    });

    // ---- Authenticated -----------------------------------------------------
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Company search (used by branch users) + public company details
        Route::get('/companies/search', [CompanyController::class, 'search'])->middleware('throttle:search');
        Route::get('/companies/{company}', [CompanyController::class, 'showPublic'])->whereNumber('company');

        // Faculty
        Route::middleware('role:faculty')->prefix('faculty')->group(function () {
            Route::get('/profile', [FacultyProfileController::class, 'show']);
            Route::put('/profile', [FacultyProfileController::class, 'update']);
        });

        // Main campus (company_main)
        Route::middleware('role:company_main')->group(function () {
            Route::post('/company', [CompanyController::class, 'store']);
            Route::get('/company', [CompanyController::class, 'show']);
            Route::put('/company', [CompanyController::class, 'update']);

            Route::get('/branches', [BranchController::class, 'index']);
            Route::get('/branches/requests', [BranchRequestController::class, 'index']);
            Route::post('/branches/requests/{branchRequest}/approve', [BranchRequestController::class, 'approve'])
                ->whereNumber('branchRequest')->middleware('throttle:actions');
            Route::post('/branches/requests/{branchRequest}/reject', [BranchRequestController::class, 'reject'])
                ->whereNumber('branchRequest')->middleware('throttle:actions');
        });

        // Branch user (company_branch)
        Route::middleware('role:company_branch')->group(function () {
            Route::post('/branches/requests', [BranchRequestController::class, 'store'])->middleware('throttle:actions');
            Route::get('/branches/requests/mine', [BranchRequestController::class, 'mine']);
            Route::post('/branches/requests/{branchRequest}/cancel', [BranchRequestController::class, 'cancel'])->whereNumber('branchRequest');
            Route::get('/branch/dashboard', [BranchController::class, 'dashboard']);
        });

        // Shared (authorization enforced by policies)
        Route::get('/branches/requests/{branchRequest}', [BranchRequestController::class, 'show'])->whereNumber('branchRequest');
        Route::get('/branches/{branch}', [BranchController::class, 'show'])->whereNumber('branch');
        Route::get('/branches/{branch}/jobs', [BranchController::class, 'jobs'])->whereNumber('branch');
    });
});
