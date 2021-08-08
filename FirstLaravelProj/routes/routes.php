<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Api\Users\UsersController;
use App\Http\Controllers\Api\Wikis\WikisController;
use App\Http\Controllers\Api\Plans\PlansController;
use App\Http\Controllers\Api\Plans\AddonsController;
use App\Http\Controllers\Api\Payments\TransactionsController;
use App\Http\Controllers\Api\Payments\CurrenciesController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Api\Taxes\TaxesController;
use App\Http\Controllers\Api\Webhooks\WebhooksController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('auth/')->group(function () {
    Route::get('login/{provider}', [SocialAuthController::class, 'redirect']);
    Route::get('login/{provider}/callback', [SocialAuthController::class, 'callback']);

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // reset password routes
    Route::post('password-reset-create', [ResetPasswordController::class, 'createFromApi']);
    Route::get('password-reset-find/{token}', [ResetPasswordController::class, 'findFromApi']);
    Route::post('reset-password', [ResetPasswordController::class, 'resetFromApi']);

    Route::middleware('auth:api')->group(function () {
        Route::get('check-token', [AuthController::class, 'checkToken']);
        Route::get('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [UsersController::class, 'getAuthUserInfo']);

    Route::get('/users/get-setup-intent', [UsersController::class, 'getAuthUserSetupIntent']);
    Route::get('/users/get-payment-methods', [UsersController::class, 'getAuthUserPaymentMethods']);
    Route::put('/users/add-payment-method', [UsersController::class, 'addAuthUserPaymentMethod']);
    Route::delete('/users/payment-methods/{id}', [UsersController::class, 'deleteAuthUserPaymentMethod']);
    Route::get('/users/invoices', [UsersController::class, 'getAuthUserInvoices']);
    
    Route::get('/wikis/check-subdomain-unique', [WikisController::class, 'checkSubdomainUnique']);
    Route::post('/wikis/create-draft-wiki', [WikisController::class, 'createDraftWiki']);
    Route::put('/wikis/set-plan-to-wiki', [WikisController::class, 'assignPlanToWiki']);
    Route::put('/wikis/add-addons-to-wiki', [WikisController::class, 'addAddonsToWiki']);
    Route::put('/wikis/remove-addons-from-wiki', [WikisController::class, 'removeAddonsFromWiki']);
    Route::get('/wikis/not-wiki-addons', [WikisController::class, 'getNotWikiAddons']);
    Route::put('/wikis/subscribe-plan', [WikisController::class, 'subscribePlan']);
    Route::put('/wikis/deactivate', [WikisController::class, 'deactivate']);
    Route::get('/wikis/langs-list', [WikisController::class, 'getWikiLangsList']);
    
    Route::resource('wikis', WikisController::class)->only([
        'index', 'store', 'show', 'update', 'destroy'
    ]);
});
