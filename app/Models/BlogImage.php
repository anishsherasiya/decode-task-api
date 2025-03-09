<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'blog_id',
        'image_path', 
    ];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
