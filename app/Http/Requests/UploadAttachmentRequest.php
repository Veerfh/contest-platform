<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Attachment;

class UploadAttachmentRequest extends FormRequest
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
            'file' => [
                'required',
                'file',
                'mimes:pdf,zip,png,jpg',
                'max:' . (Attachment::MAX_SIZE / 1024),
            ],
        ];
    }
}