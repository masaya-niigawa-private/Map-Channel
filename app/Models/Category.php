<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
  use HasFactory;

  protected $fillable = ['name', 'sort_order']; // name, sort_order【カテゴリ項目】
  protected $casts = ['sort_order' => 'integer'];

  public function boards()
  {
    return $this->hasMany(Board::class);
  }
}
