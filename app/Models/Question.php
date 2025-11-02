<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Question extends Model
{
  protected $fillable = ['author_id', 'title', 'body', 'status', 'is_resolved'];

  public function answers()
  {
    return $this->hasMany(Answer::class);
  }

  public function tags()
  {
    return $this->belongsToMany(Tag::class, 'question_tag');
  }
}
