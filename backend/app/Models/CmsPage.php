<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $fillable = ['title', 'slug', 'content', 'meta_title', 'meta_description', 'is_published', 'published_at'];
    protected function casts(): array { return ['is_published' => 'boolean', 'published_at' => 'datetime']; }
}

