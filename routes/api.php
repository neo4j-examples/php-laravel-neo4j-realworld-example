<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProfileController;
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
    Route::delete('/articles/feed', 'listArticles');
    Route::get('/articles', 'queryArticles')->middleware('auth');
    Route::post('/articles', 'createArticle')->middleware('auth');
    Route::get('/articles/{slug}', 'getArticle');
    Route::put('/articles/{slug}', 'updateArticle')->middleware('auth');
    Route::delete('/articles/{slug}', 'deleteArticle')->middleware('auth');
});

Route::controller(UserController::class)->group(function () {
    Route::post('/users/login', 'login');
    Route::post('/users', 'create');
    Route::get('/user', 'get')->middleware('auth');
    Route::put('/user', 'update')->middleware('auth');
});

Route::controller(ProfileController::class)->group(function () {
    Route::get('/profiles/{username}', 'getProfile');
    Route::post('/profiles/{username}/follow', 'followProfile')->middleware('auth');
    Route::delete('/profiles/{username}/follow', 'unfollowProfile')->middleware('auth');
});

Route::controller(CommentController::class)->group(function () {
    Route::get('/articles/{slug}/comments', 'getComments');
    Route::post('/articles/{slug}/comments', 'comment')->middleware('auth');
    Route::delete('/articles/{slug}/comments/{id}', 'uncomment')->middleware('auth');
});

Route::controller(FavoriteController::class)->group(function () {
    Route::post('/articles/{slug}/favorite', 'favorite')->middleware('auth');
    Route::delete('/articles/{slug}/favorite', 'unfavorite')->middleware('auth');
});

// todo - create tag feed
