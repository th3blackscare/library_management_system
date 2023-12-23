<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\ApiAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


/**
 * i have added a global middleware on top of API routing called ForceJsonResponse @uses \app\Http\Middleware\ForceJsonResponse
 * to the application's global HTTP middleware stack @uses \app\Http\Kernel
 * to force the response to be json, to properly handle the response and errors in the frontend
**/
Route::post('/login', [ApiAuthController::class, 'Login']);
Route::post('/register', [ApiAuthController::class, 'register']);
