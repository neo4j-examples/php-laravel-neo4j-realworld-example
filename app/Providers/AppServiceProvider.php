<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use App\Observers\ArticleObserver;
use App\Observers\CommentObserver;
use DomainException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use UnexpectedValueException;
use function preg_replace;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        Auth::viaRequest('jwt', static function (Request $request) {
             $token = $request->header('Authorization', null);

             if ($token === null) {
                 return null;
             }

             try {
                 $token = (array) JWT::decode(preg_replace('/^(Token|Bearer) /', '', $token), new Key(Config::get('app.key'), 'HS256'));
             } catch (UnexpectedValueException|DomainException|InvalidArgumentException) {
                 return null;
             }

             $username = ((array) ($token['user'] ?? []))['username'] ?? null;

             return $username === null ? null : User::query()->find($username);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Article::observe(ArticleObserver::class);
        Comment::observe(CommentObserver::class);
    }
}
