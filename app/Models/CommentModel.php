<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Vinelab\NeoEloquent\Eloquent\Model;

class CommentModel extends Model
{
    use HasFactory;

    protected $table = 'Comment';
}
