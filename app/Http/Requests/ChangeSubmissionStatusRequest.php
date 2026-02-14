<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Submission;

class ChangeSubmissionStatusRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->isJury();
    }

    public function rules()
    {
        return [
            'status' => 'required|in:submitted,needs_fix,accepted,rejected',
        ];
    }
}