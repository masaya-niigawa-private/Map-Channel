<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class QuestionStoreRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  } // 認証は後で

  public function rules(): array
  {
    return [
      'title' => ['required', 'string', 'max:160'],
      'body' => ['required', 'string', 'max:65535'],
      'status' => ['nullable', 'in:published,draft'],
      'tags' => ['array'],
      'tags.*' => ['string', 'max:48'],
    ];
  }
}
