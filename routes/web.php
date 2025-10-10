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

// route::middleware(['auth','role:admin'])->prefix('admin')->group(function()
// {
//     route::get('/dashboard')
// }
//)