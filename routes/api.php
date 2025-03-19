<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Api\MessageContactController;
use App\Http\Controllers\Api\MessageTemplateController;

Route::controller(AuthenticationController::class)->group(function () {
    Route::post('/sign-up', 'signUp');
    Route::post('/sign-in', 'signIn');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/get-user-data', 'getAuthUserData');
        Route::post('/auth/update-user-data', 'updateAuthUserData');
        Route::post('/auth/sign-out', 'signOut');
        Route::post('/auth/sign-out-all', 'signOutFromAllDevices');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/contact/store', [MessageContactController::class, 'contactStore']);
    Route::get('/contact/list', [MessageContactController::class, 'contactList']);
    Route::get('/contact/detail/{id}', [MessageContactController::class, 'contactDetail']);
    Route::post('/contact/update/{id}', [MessageContactController::class, 'contactUpdate']);

    Route::post('/template/store', [MessageTemplateController::class, 'templateStore']);
});
