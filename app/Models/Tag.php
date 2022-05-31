<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Vinelab\NeoEloquent\Eloquent\Model;

/**
 * @property string $name
 */
class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
}
