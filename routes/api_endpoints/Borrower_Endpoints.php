<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BorrowerController;



/**
 * all endpoints in this file are prefixed with /api/books
 * this prefix can be changed in the file @uses \app\Providers\RouteServiceProvider
 * all endpoints in this file needs the user to be authenticated.
 **/

Route::middleware('auth:api')->group(function (){
    Route::get('list',[BorrowerController::class,'list']);
    // we will use a custom middleware to make sure that only an admin can add new , update  or delete a book
    Route::post('add',[BorrowerController::class,'add']);
    Route::post('update',[BorrowerController::class,'update']);
    Route::post('delete',[BorrowerController::class,'delete']);
});
