<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Board extends Model
{
  use HasFactory, SoftDeletes; // SoftDeletes は boards に適用

  protected $fillable = [
    'author_id',
    'category_id',
    'photo_path',
    'location_name',
    'location_lat',
    'location_lng',
    'description',
    'link_url',
    'view_count',
    'favorite_count',
  ];

  protected $casts = [
    'location_lat' => 'float',
    'location_lng' => 'float',
    'view_count' => 'integer',
    'favorite_count' => 'integer',
  ];

  // 返却APIは photo_url を期待するためアクセサを用意（任意）
  protected $appends = ['photo_url'];

  public function getPhotoUrlAttribute(): ?string
  {
    return $this->photo_path ? Storage::url($this->photo_path) : null;
  }

  // relations
  public function author()
  {
    return $this->belongsTo(User::class, 'author_id');
  }

  public function category()
  {
    return $this->belongsTo(Category::class);
  }

  public function comments()
  {
    return $this->hasMany(BoardComment::class);
  }

  public function favorites()
  {
    return $this->hasMany(BoardFavorite::class);
  }

  public function reports()
  {
    return $this->hasMany(BoardReport::class);
  }
}
