<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BorrowerOperationsController;



/**
 * all endpoints in this file are prefixed with /api/borrowing
 * this prefix can be changed in the file @uses \app\Providers\RouteServiceProvider
 * all endpoints in this file needs the user to be authenticated.
 **/

Route::middleware('auth:api')->group(function (){
    Route::middleware('throttle:3,10')->get('listOverdueBooks',[BorrowerOperationsController::class,'listOverdueBooks']);
    Route::get('exportLastMonthBorrowers',[BorrowerOperationsController::class,'exportLastMonthBorrowers']);
    Route::get('exportLastMonthOverdue',[BorrowerOperationsController::class,'exportLastMonthOverdue']);
    Route::post('return',[BorrowerOperationsController::class,'returnBook']);
    Route::post('borrow',[BorrowerOperationsController::class,'borrow']);
});
