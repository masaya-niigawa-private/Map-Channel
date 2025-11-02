<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

final class QuestionResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'id' => $this->id,
      'author_id' => $this->author_id,
      'title' => $this->title,
      'body' => $this->body,
      'is_resolved' => (bool) $this->is_resolved,
      'answers_count' => (int) $this->answers_count,
      'views_count' => (int) $this->views_count,
      'bookmarks_count' => (int) $this->bookmarks_count,
      'status' => $this->status,
      'best_answer_id' => $this->best_answer_id,
      'tags' => $this->whenLoaded('tags', function () {
        return $this->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name]);
      }),
      'created_at' => $this->created_at,
    ];
  }
}
