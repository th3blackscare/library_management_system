<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BorrowerController;
use App\Http\Controllers\BorrowerOperationsController;



/**
 * all endpoints in this file are prefixed with /api/borrower
 * this prefix can be changed in the file @uses \app\Providers\RouteServiceProvider
 * all endpoints in this file needs the user to be authenticated.
 **/

Route::middleware('auth:api')->group(function (){
    Route::get('list',[BorrowerController::class,'list']);
    Route::post('add',[BorrowerController::class,'add']);
    Route::post('update',[BorrowerController::class,'update']);
    Route::post('delete',[BorrowerController::class,'delete']);
    Route::post('myBooks',[BorrowerOperationsController::class,'myBooks']);
});
