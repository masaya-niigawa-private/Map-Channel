<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardReport extends Model
{
  use HasFactory;

  protected $fillable = ['board_id', 'reporter_id', 'reason'];

  public function board()
  {
    return $this->belongsTo(Board::class);
  }

  public function reporter()
  {
    return $this->belongsTo(User::class, 'reporter_id');
  }
}
