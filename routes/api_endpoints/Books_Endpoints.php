<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookOperationsController;



/**
 * all endpoints in this file are prefixed with /api/books
 * this prefix can be changed in the file @uses \app\Providers\RouteServiceProvider
 * all endpoints in this file needs the user to be authenticated.
 **/

Route::middleware('auth:api')->group(function (){
    Route::any('list',[BookOperationsController::class,'ListAllBooks']);
});
