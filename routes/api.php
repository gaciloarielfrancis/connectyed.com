<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PostsController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\SpecialtiesController;
use App\Http\Controllers\API\AvailabilityController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\MatchmakerController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\API\GoogleMeetController;
use App\Http\Controllers\API\ZoomController;
use App\Http\Controllers\API\NewsletterController;
use App\Http\Controllers\API\MeetingController;
use App\Http\Controllers\API\CriteriaController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and are assigned to the
| "api" middleware group. Make something great!
|
*/
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->middleware('throttle:5,1'); // 5 attempts per minute
    // Public route to get minimal client data for a matchmaker
Route::get('/public/matchmaker/clients/{matchmakerId}', [ClientController::class, 'getPublicClientsByMatchmakerId']);

Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:5,1'); // 5 attempts per minute
Route::post('password/reset', [ForgotPasswordController::class, 'reset'])->middleware('throttle:5,1');

// Google OAuth

// Meetings
Route::post('/google/create-meeting', [GoogleMeetController::class, 'createMeeting'])->middleware('auth:api');
Route::get('/google/check-authorization', [GoogleMeetController::class, 'checkGoogleAuthorization'])->middleware('auth:api');
Route::get('/google/upcoming-meetings', [GoogleMeetController::class, 'getUpcomingMeetings'])->middleware('auth:api');

Route::get('google/meetings/{id}', [GoogleMeetController::class, 'getMeeting']);

// Zoom
Route::post('zoom/pay-to-schedule', [ZoomController::class, 'payForMeeting'])->middleware('auth:api');
Route::get('zoom/schedule-meeting/{token}', [ZoomController::class, 'scheduleMeeting']);
Route::post('zoom/create-a-meeting', [ZoomController::class, 'createAMeeting']);

// Token Management
Route::post('/google/refresh-token', [GoogleMeetController::class, 'refreshGoogleToken'])->middleware('auth:api');
Route::post('/google/revoke-token', [GoogleMeetController::class, 'revokeGoogleToken'])->middleware('auth:api');

// Authentication Routes
Route::prefix('user')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('introspect', [AuthController::class, 'introspect'])->middleware('auth:api');
    Route::post('resend-verification-email', [AuthController::class, 'resendVerificationEmail'])->middleware('auth:api');
});

// Posts Routes
Route::resource('post', PostsController::class)->only(['index', 'show']);
Route::get('post/featured', [PostsController::class, 'featured']);

// Profile Routes
Route::middleware('auth:api')->prefix('profile')->group(function () {
    Route::controller(ProfileController::class)->group(function () {
        Route::put('update', 'updateprofile');
        Route::get('getprofile', 'getprofile');
        Route::get('getdetail/{username}', 'getdetail');
        Route::get('images', 'profileimages');
        Route::post('uploadimages', 'uploadimages');
        Route::put('updateavatar', 'updateavatar');
        Route::post('uploadavatar', 'uploadavatar');
    });
});

Route::get('user/introspect', [AuthController::class, 'introspect'])->middleware('auth:api');

// Specialties Routes
Route::middleware('auth:api')->prefix('specialties')->group(function () {
    Route::controller(SpecialtiesController::class)->group(function () {
        Route::put('update', 'updatespecialties');
        Route::get('getspecialties', 'getspecialties');
        Route::get('getspecialties/{username}', 'getSpecialtiesByUsername');
    });
});

// routes/api.php
Route::middleware(['auth:api'])->group(function () {
    Route::prefix('matchmaker')->group(function () {
        Route::post('/clients', [ClientController::class, 'addClients']);
        Route::get('/clients/{matchmakerId}', [ClientController::class, 'getClientsByMatchmakerId']);
    });
});

// Clients Routes
Route::middleware('auth:api')->get('/clients', [ClientController::class, 'index']);
Route::middleware('auth:api')->get('/clients/all', [ClientController::class, 'getAllClients']);

// Availability Routes
Route::middleware('auth:api')->group(function () {
    Route::post('/availabilities', [AvailabilityController::class, 'store']);
    Route::get('/availabilities/{user_id}', [AvailabilityController::class, 'index']);
    Route::delete('/availabilities/{id}', [AvailabilityController::class, 'destroy']); // Added
});

// In api.php
Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\API\VerificationController::class, 'verify'])
    ->name('verification.verify');





// Admin Routes
Route::middleware('auth:api')->prefix('admin')->group(function () {
    Route::get('candidates', [AdminController::class, 'getCandidates']);
    Route::post('candidates/approve', [AdminController::class, 'approveCandidate']);
    Route::get('clients', [AdminController::class, 'getClients']);
});



// Matchmakers List (Public Route)
Route::get('/matchmakers', [MatchmakerController::class, 'index']);


// Matchmaker Earnings Routes
Route::middleware('auth:api')->group(function () {
    Route::get('/matchmaker/earnings', [MatchmakerController::class, 'getEarnings']);
    Route::post('/matchmaker/request-withdrawal', [MatchmakerController::class, 'requestWithdrawal']);
});

// Messaging Routes
Route::middleware('auth:api')->group(function () {
    Route::post('/messages/send', [MessageController::class, 'sendMessage']);
    Route::get('/messages', [MessageController::class, 'getMessages']);
});

// User routes
Route::middleware('auth:api')->group(function () {
    Route::post('/user/criteria', [UserController::class, 'updateCriteria']);
    Route::get('/user/package-info', [UserController::class, 'getPackageInfo']);
});



// Payment Routes
Route::middleware('auth:api')->group(function () {
    Route::post('/billing/create-checkout-session', [PaymentController::class, 'createCheckoutSession']);
    Route::get('/payment/success/{product_type}', [PaymentController::class, 'success'])
        ->name('product.payment.success');
    Route::get('/payment/cancel', [PaymentController::class, 'cancel'])
        ->name('product.payment.cancel');
});

Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->name('stripe.webhook')
    ->withoutMiddleware([
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Session\Middleware\StartSession::class,
    ]);

Route::middleware('auth:api')->group(function () {
    Route::post('/user/criteria', [CriteriaController::class, 'updateCriteria']);
    Route::get('/user/criteria', [CriteriaController::class, 'getCriteria']);
});

Route::prefix('api/matchmaker')->group(function () {
    Route::post('/clients', [ClientController::class, 'addClients']);
    Route::get('/clients/{matchmakerId}', [ClientController::class, 'getClientsByMatchmakerId']);
});

Route::put('/matchmaker/clients/{clientId}', [ClientController::class, 'updateClient'])
    ->middleware('auth:api');
    
    
Route::middleware(['auth:api'])->group(function () {
    Route::put('/profile/updateprofile', [ProfileController::class, 'updateprofile']);
    Route::post('/profile/uploadimage', [ProfileController::class, 'uploadimage']);
    Route::get('/profile/getprofile', [ProfileController::class, 'getprofile']);
    Route::get('/profile/getimages', [ProfileController::class, 'getimages']);
});

Route::get('/profile/detail/{username}', [ProfileController::class, 'getdetail']);