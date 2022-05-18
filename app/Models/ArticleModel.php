<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Vinelab\NeoEloquent\Eloquent\Model;

class ArticleModel extends Model
{
    use HasFactory;

    protected $table = 'Article';

    protected $primaryKey = 'slug';
}
