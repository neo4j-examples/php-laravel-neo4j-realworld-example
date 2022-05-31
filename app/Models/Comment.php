<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Vinelab\NeoEloquent\Eloquent\Model;

/**
 * @property int $id
 * @property Carbon $createdAt
 * @property Carbon $updatedAt
 * @property string $body
 */
class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['body'];
}
