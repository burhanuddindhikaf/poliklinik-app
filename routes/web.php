<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Auth\Events\Login;

Route::get('/', function () {
    return view('welcome');
});

route::get('/login', [AuthController::class, 'showLogin'])->name('login');
route::post('/login', [AuthController::class, 'login']);
route::get('/register', [AuthController::class, 'showRegister'])->name('register');
route::post('/register', [AuthController::class, 'register']);
route::post('/logout', [AuthController::class, 'logout']);

route::middleware(['auth','role:admin'])->prefix('admin')->group(function()
{
    route::get('/dashboard', function() {
        return view('admin.dashboard');
    })->name('admin.dashboard');
}
);
route::middleware(['auth','role:dokter'])->prefix('dokter')->group(function()
{
    route::get('/dashboard', function() {
        return view('dokter.dashboard');
    })->name('dokter.dashboard');
}
);
route::middleware(['auth','role:pasien'])->prefix('pasien')->group(function()
{
    route::get('/dashboard', function() {
        return view('pasien.dashboard');
    })->name('pasien.dashboard');
}
);