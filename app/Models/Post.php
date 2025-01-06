<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class Post extends Model
{
    protected $fillable = ['title', 'content'];

    public static function fetchPaginatedBlogs($perPage = 10)
    {
        return self::orderBy('id', 'desc')->paginate($perPage);
    }
}
