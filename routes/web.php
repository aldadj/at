<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::get('/', [ChatController::class, 'index'])->name('at.chat');

Route::get('/at/{id?}', [ChatController::class, 'index'])->name('at.chat');
Route::post('/at/send', [ChatController::class, 'store'])->name('at.send');
Route::delete('/at/{conversation}', [ChatController::class, 'destroy'])->name('at.delete');