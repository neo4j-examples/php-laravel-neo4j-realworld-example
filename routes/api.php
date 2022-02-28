<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(ArticleController::class)->group(function () {
    Route::get('/articles', 'listArticles');
    Route::post('/articles', 'createArticle');
    Route::get('/articles/{slug}', 'getArticle');
    Route::put('/articles/{slug}', 'updateArticle');
    Route::delete('/articles/{slug}', 'deleteArticle');
});

Route::controller(UserController::class)->group(function () {
    Route::post('/users/login', 'login');
    Route::post('/users', 'create');
    Route::get('/user', 'get');
    Route::put('/user', 'update');
});
