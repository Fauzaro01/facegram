<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'caption',
        'user_id'
    ];

    public function user() {
        $this->belongsTo(User::class);
    }

    public function postattach() {
        $this->hasMany(PostAttach::class);
    }
    
}
