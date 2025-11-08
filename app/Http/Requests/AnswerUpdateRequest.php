<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnswerUpdateRequest extends FormRequest
{
  public function authorize(): bool
  {
    // 認証導入前は true。導入後はポリシーで制御
    return true;
  }

  public function rules(): array
  {
    return [
      'body' => ['sometimes', 'required', 'string', 'max:65535'],
    ];
  }
}
