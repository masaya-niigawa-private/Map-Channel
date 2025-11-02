<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class AnswerResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'id' => $this->id,
      'question_id' => $this->question_id,
      'author_id' => $this->author_id,
      'body' => $this->body,
      'is_best' => (bool) $this->is_best,
      'upvotes_count' => (int) $this->upvotes_count,
      'status' => $this->status,
      'created_at' => $this->created_at,
    ];
  }
}
