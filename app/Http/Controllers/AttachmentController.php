<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Attachment;
use App\Services\AttachmentService;
use App\Http\Requests\UploadAttachmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    private AttachmentService $attachmentService;

    public function __construct(AttachmentService $attachmentService)
    {
        $this->attachmentService = $attachmentService;
    }

    public function upload(Request $request, Submission $submission)
    {
        $user = auth()->user();
        if ($user->id !== $submission->user_id) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }
        
        if (!$submission->isEditable()) {
            return response()->json(['error' => 'Нельзя загружать файлы в текущем статусе работы'], 422);
        }
        
        if ($submission->attachments()->count() >= Attachment::MAX_FILES_PER_SUBMISSION) {
            return response()->json(['error' => 'Достигнуто максимальное количество файлов (3)'], 422);
        }
        
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'Файл не выбран'], 422);
        }
        
        $file = $request->file('file');
        
        if (!$file->isValid()) {
            return response()->json(['error' => 'Ошибка при загрузке файла: ' . $file->getErrorMessage()], 422);
        }
        
        if ($file->getSize() > Attachment::MAX_SIZE) {
            return response()->json(['error' => 'Файл слишком большой. Максимальный размер: 10MB'], 422);
        }
        
        $mime = $file->getMimeType();
        if (!in_array($mime, Attachment::ALLOWED_MIMES)) {
            return response()->json(['error' => 'Недопустимый тип файла. Разрешенные форматы: PDF, ZIP, PNG, JPG'], 422);
        }
        
        try {
            $attachment = $this->attachmentService->upload(
                $submission,
                $file,
                auth()->id()
            );
            
            return response()->json($attachment, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка при сохранении файла: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Скачивание файла
     */
    public function download(Attachment $attachment)
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        if (!$user->isJury() && !$user->isAdmin() && $user->id !== $attachment->user_id) {
            abort(403, 'Доступ запрещен');
        }
        
        if ($attachment->status !== 'scanned') {
            abort(403, 'Файл еще не доступен для скачивания');
        }

        try {
            $filePath = $attachment->storage_key;
            
            if (!Storage::disk('s3')->exists($filePath)) {
                abort(404, 'Файл не найден');
            }
            
            $fileContent = Storage::disk('s3')->get($filePath);
            
            return response($fileContent)
                ->header('Content-Type', $attachment->mime)
                ->header('Content-Disposition', 'attachment; filename="' . $attachment->original_name . '"')
                ->header('Content-Length', $attachment->size);
                
        } catch (\Exception $e) {
            Log::error('Download error', [
                'error' => $e->getMessage(),
                'attachment_id' => $attachment->id
            ]);
            abort(500, 'Ошибка при скачивании файла');
        }
    }
}