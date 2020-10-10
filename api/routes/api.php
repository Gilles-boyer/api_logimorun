<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//register
Route::post("create-user", [UserController::class, 'store']);
//login
Route::post("login-user", [UserController::class, 'login']);

Route::group(['middleware' => 'auth:api'], function () {

    Route::get ("users",                [UserController::class, 'index']);
    Route::get ("user/{id}",            [UserController::class, 'show']);
    Route::post("user/update",          [UserController::class, 'update']);
    Route::get ("user/logout/me",       [UserController::class, 'logout']);
    Route::post("user/update/password", [UserController::class, 'updatePassword']);
    Route::post("user/delete",          [UserController::class, 'destroy']);



});
