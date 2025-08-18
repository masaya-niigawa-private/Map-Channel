<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spot extends Model
{
    protected $fillable = [
        'spot_name',
        'evaluation'
    ];
    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

}
