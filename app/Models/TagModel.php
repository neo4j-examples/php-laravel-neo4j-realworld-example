<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Vinelab\NeoEloquent\Eloquent\Model;

class TagModel extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    protected $table = 'Tag';
}
