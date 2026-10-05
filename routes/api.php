<?php

declare(strict_types=1);

use App\Http\Controllers\Assistant\CompanionController;
use App\Http\Controllers\Assistant\ReliabilityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Assistant API
|--------------------------------------------------------------------------
|
| Deliberately small. The AI surface exists to be operated, not to be a
| product of its own, so the endpoints cover exactly what a visitor needs and
| what an interviewer needs to verify.
|
*/

Route::post('/assistant/message', [CompanionController::class, 'store'])
    ->name('assistant.message');

Route::post('/assistant/stream', [CompanionController::class, 'stream'])
    ->name('assistant.stream');

Route::get('/assistant/telemetry', ReliabilityController::class)
    ->name('assistant.telemetry');
