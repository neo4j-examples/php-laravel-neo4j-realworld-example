<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\User;
use App\Models\UserModel;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laudis\Neo4j\Basic\Session;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('change-article', function (UserModel $user, string $slug) {
            /** @var Session $session */
            $session = $this->app->get(Session::class);

            return !$session->run(<<<'CYPHER'
            MATCH (u:User {username: $user}) - [:AUTHORED] -> (a:Article {slug: $slug})
            RETURN u
            CYPHER, ['user' => $user->getAuthIdentifier(), 'slug' => $slug])
                ->isEmpty();
        });
    }
}
