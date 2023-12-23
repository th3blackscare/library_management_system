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
    // we will use a custom middleware to make sure that only an admin can add new , update  or delete a book
    Route::middleware('hasAdminRole')->any('add',[BookOperationsController::class,'addBook']);
    Route::middleware('hasAdminRole')->any('update',[BookOperationsController::class,'update']);
    Route::middleware('hasAdminRole')->any('delete',[BookOperationsController::class,'delete']);
});
