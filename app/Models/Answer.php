<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Answer extends Model
{
  protected $fillable = ['question_id', 'author_id', 'body', 'status'];

  public function question()
  {
    return $this->belongsTo(Question::class);
  }
}
