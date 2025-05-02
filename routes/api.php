<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PollController;
use App\Http\Controllers\API\PollOptionsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::apiResource('poll', PollController::class);
Route::apiResource('poll-option', PollOptionsController::class);
Route::post('poll-option/{id}/vote', [PollOptionsController::class, 'vote']);