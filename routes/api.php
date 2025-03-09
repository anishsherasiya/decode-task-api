<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CreditSystemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;


Route::controller(AuthController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login')->middleware('throttle:5,1');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'getUser']);
    Route::post('/logout',[UserController::class,'logout']);

    Route::get('/blog', [BlogController::class, 'index']);
    Route::post('/blog', [BlogController::class, 'store'])->middleware('throttle:5,1');
    Route::get('/blog/{id}', [BlogController::class, 'show']);
    Route::put('/blog/{id}', [BlogController::class, 'update']);
    Route::delete('/blog/{id}', [BlogController::class, 'destroy']);

    Route::post('/purchase-credits', [CreditSystemController::class, 'purchaseCredit']);
    Route::get('/credits', [CreditSystemController::class, 'getCredit']);

});

Route::post('/webhook', [WebhookController::class, 'handleWebhook']);