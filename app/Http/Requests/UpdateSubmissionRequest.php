<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubmissionRequest extends FormRequest
{
    public function authorize()
    {
        $submission = $this->route('submission');
        return auth()->check() && 
               auth()->id() === $submission->user_id && 
               $submission->isEditable();
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
        ];
    }
}