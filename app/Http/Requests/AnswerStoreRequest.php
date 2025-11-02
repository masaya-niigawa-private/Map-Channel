<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AnswerStoreRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  } // 認証は後で

  public function rules(): array
  {
    return [
      'body' => ['required', 'string', 'max:65535'],
    ];
  }
}
