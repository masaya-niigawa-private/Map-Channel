<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuestionUpdateRequest extends FormRequest
{
  public function authorize(): bool
  {
    // 認証導入前は true。導入後はポリシーで制御
    return true;
  }

  public function rules(): array
  {
    return [
      'title' => ['sometimes', 'required', 'string', 'max:160'],
      'body' => ['sometimes', 'required', 'string', 'max:65535'],
      'status' => ['sometimes', 'in:published,draft'],
      'tags' => ['nullable', 'array'],
      'tags.*' => ['string', 'max:48'],
    ];
  }
}
