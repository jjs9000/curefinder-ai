<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\SocialiteController;
use App\Livewire\AiAgent\ChatBubble as AiAgentChatBubble;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

// GitHub login
Route::get('auth/github', [SocialiteController::class, 'redirectToGithub'])->name('auth.github');
Route::get('auth/github/callback', [SocialiteController::class, 'handleGithubCallback']);

// AI Chat Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/chat', AiAgentChatBubble::class);
});

require __DIR__ . '/auth.php';
