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

Route::controller(ProfileController::class)->group(function () {
    Route::get('/profiles/{username}', 'getProfile');
    Route::post('/profiles/{username}/follow', 'followProfile');
    Route::delete('/profiles/{username}/follow', 'unfollowProfile');
});

Route::controller(CommentController::class)->group(function () {
    Route::get('/articles/{slug}/comments', 'getComments');
    Route::post('/articles/{slug}/comments', 'comment');
    Route::delete('/articles/{slug}/comments/{id}', 'uncomment');
});

Route::controller(FavoriteController::class)->group(function () {
    Route::post('/articles/{slug}/favorite', 'favorite');
    Route::delete('/articles/{slug}/favorite', 'unfavorite');
});
