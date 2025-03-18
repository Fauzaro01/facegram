<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Follow extends Model
{
    protected $fillable = [
        'follower_id',
        'following_id',
        'is_accepted',
    ];

    public function follower() {
        return $this->belongsTo(User::class, 'follower_id');
    }
    public function following() {
        return $this->belongsTo(User::class, 'following_id');
    }
}
