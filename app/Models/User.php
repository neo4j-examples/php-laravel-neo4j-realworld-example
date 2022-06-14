<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\Relations\HasMany;

/**
 * @property string $username
 * @property string $email
 * @property string $bio
 * @property string $image
 * @property string $passwordHash
 */
class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use HasApiTokens,
        HasFactory,
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

    public function writtenArticles(): HasMany
    {
        return $this->hasManyRelationship(Article::class, 'AUTHORED');
    }

    public function favorited(): BelongsToMany
    {
        return $this->belongsToManyRelation(Article::class, 'FAVORITED>');
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToManyRelation(__CLASS__, '<FOLLOWING');
    }

    public function secondDegreeFollowers(): HasManyThrough
    {
        return $this->hasManyThroughRelation(__CLASS__, __CLASS__, '<FOLLOWING', '<FOLLOWING');
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToManyRelation(__CLASS__, 'FOLLOWING>');
    }
}
