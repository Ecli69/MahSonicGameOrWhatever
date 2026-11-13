<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () { return redirect()->route('game.index'); });

// Гости
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Авторизованные
Route::middleware('auth')->group(function () {
    Route::get('/account', [App\Http\Controllers\AccountController::class, 'index'])->name('account.index');
    Route::post('/account', [App\Http\Controllers\AccountController::class, 'update'])->name('account.update');
    Route::post('/account/delete-record', [App\Http\Controllers\AccountController::class, 'deleteRecord'])->name('account.deleteRecord');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/game', [GameController::class, 'index'])->name('game.index');
    Route::post('/game/save', [GameController::class, 'saveScore'])->name('game.save');
    Route::get('/leaderboard', [GameController::class, 'leaderboard'])->name('game.leaderboard');
});