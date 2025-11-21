<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'news';

    /**
     * Các trường được phép fill.
     */
    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'thumbnail',
        'author_id',
        'view_count',
        'status',
        'published_at',
    ];

    /**
     * Các trường datetime
     */
    protected $casts = [
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
        'published_at' => 'datetime',
    ];

    /**
     * Quan hệ: News thuộc tác giả (User)
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
