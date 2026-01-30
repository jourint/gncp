<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\MessengerWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// URL будет выглядеть так: domain.com/api/messenger/telegram/твой_секретный_токен
Route::post('/messenger/{driver}/{token}', [MessengerWebhookController::class, 'handle']);
