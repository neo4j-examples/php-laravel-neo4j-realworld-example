<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\Models\Relations\Relationship;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property string $username
 * @property string $email
 * @property string $bio
 * @property string $image
 * @property string $passwordHash
 */
class User extends Node implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use HasApiTokens,
        Notifiable,
        Authenticatable,
        Authorizable,
        CanResetPassword;

    protected $primaryKey = 'username';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'bio',
        'image',
        'passwordHash',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'passwordHash',
    ];

    public function getAuthIdentifier(): string
    {
        return $this->username;
    }

    public function writtenArticles(): Relationship
    {
        return $this->matchRelationship(Article::class, 'AUTHORED>');
    }

    public function favorited(): Relationship
    {
        return $this->matchRelationship(Article::class, 'FAVORITED>');
    }

    public function followers(): Relationship
    {
        return $this->matchRelationship(__CLASS__, '<FOLLOWING');
    }

    public function following(): Relationship
    {
        return $this->matchRelationship(__CLASS__, 'FOLLOWING>');
    }
}
