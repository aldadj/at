<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

// Une seule route pour l'index (l'accueil et l'ID optionnel)
Route::get('/{id?}', [ChatController::class, 'index'])->name('at.chat');

// Les routes d'action
Route::post('/at/send', [ChatController::class, 'store'])->name('at.send');
Route::delete('/at/{conversation}', [ChatController::class, 'destroy'])->name('at.delete');