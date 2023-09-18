<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubComment extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
