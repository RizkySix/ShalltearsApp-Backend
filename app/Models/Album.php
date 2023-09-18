<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function album_photos()
    {
        return $this->hasMany(AlbumPhoto::class);
    }
    
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
