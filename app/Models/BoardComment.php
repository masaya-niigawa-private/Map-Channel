<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoardComment extends Model
{
  use HasFactory, SoftDeletes; // SoftDeletes は board_comments に適用

  protected $fillable = ['board_id', 'author_id', 'content'];
  protected $casts = [];

  public function board()
  {
    return $this->belongsTo(Board::class);
  }

  public function author()
  {
    return $this->belongsTo(User::class, 'author_id');
  }
}
