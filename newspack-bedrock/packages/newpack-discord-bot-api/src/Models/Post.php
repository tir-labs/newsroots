<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'post_title',
        'post_content',
        'post_status',
        'post_type',
        'post_author',
        'post_date',
        'post_modified',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'post_author');
    }

    public function meta()
    {
        return $this->hasMany(PostMeta::class, 'post_id');
    }

    public function terms()
    {
        return $this->belongsToMany(Term::class, 'term_relationships', 'object_id', 'term_taxonomy_id');
    }

    public function scopePublished($query)
    {
        return $query->where('post_status', 'publish');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('post_type', $type);
    }
}
