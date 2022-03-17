<?php

namespace App\Providers;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Laudis\Neo4j\Basic\Driver;
use Laudis\Neo4j\Basic\Session;
use function str_replace;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(Session::class, static function() {
            return Driver::create('bolt://neo4j:test@localhost')->createSession();
        });

        Auth::viaRequest('jwt', static function (Request $request) {
             $token = $request->header('Authorization', null);

             if ($token === null) {
                 return null;
             }

             $token = (array) JWT::decode(str_replace('Bearer ', '', $token), new Key(env('APP_KEY'), 'HS256'));

             $user = new User();
             $user->setRawAttributes($token);

             return $user;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
