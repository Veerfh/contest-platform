<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\Submission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Jobs\ScanAttachmentJob;

class AttachmentService
{
    public function upload(Submission $submission, UploadedFile $file, int $userId): Attachment
    {
        if ($submission->attachments()->count() >= Attachment::MAX_FILES_PER_SUBMISSION) {
            throw new \Exception('Достигнуто максимальное количество файлов (3)');
        }
        
        $extension = $file->getClientOriginalExtension();
        $storageKey = 'submissions/' . $submission->id . '/' . date('Y/m/d/') . Str::random(40) . '.' . $extension;
        
        try {
            $fileResource = fopen($file->getRealPath(), 'r');
            
            Storage::disk('s3')->put($storageKey, $fileResource, [
                'visibility' => 'private',
                'ContentType' => $file->getMimeType(),
                'Metadata' => [
                    'original_name' => $file->getClientOriginalName(),
                    'user_id' => (string) $userId,
                    'submission_id' => (string) $submission->id
                ]
            ]);
            
            if (is_resource($fileResource)) {
                fclose($fileResource);
            }
            
            if (!Storage::disk('s3')->exists($storageKey)) {
                throw new \Exception('Файл не был сохранен в хранилище');
            }
            
            $attachment = Attachment::create([
                'submission_id' => $submission->id,
                'user_id' => $userId,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'storage_key' => $storageKey,
                'status' => Attachment::STATUS_PENDING,
            ]);
            
            dispatch(new ScanAttachmentJob($attachment));
            
            return $attachment;
            
        } catch (\Exception $e) {
            if (isset($storageKey) && Storage::disk('s3')->exists($storageKey)) {
                Storage::disk('s3')->delete($storageKey);
            }
            
            \Log::error('S3 Upload Error', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'submission_id' => $submission->id
            ]);
            
            throw new \Exception('Ошибка при загрузке файла в облачное хранилище: ' . $e->getMessage());
        }
    }

    public function markScanned(Attachment $attachment): Attachment
    {
        $attachment->status = Attachment::STATUS_SCANNED;
        $attachment->save();
        
        return $attachment;
    }

    public function reject(Attachment $attachment, string $reason): Attachment
    {
        $attachment->status = Attachment::STATUS_REJECTED;
        $attachment->rejection_reason = $reason;
        $attachment->save();
        
        return $attachment;
    }

    public function getSignedUrl(Attachment $attachment): string
    {
        if (!Storage::disk('s3')->exists($attachment->storage_key)) {
            throw new \Exception('Файл не найден в хранилище');
        }
        
        return Storage::disk('s3')->temporaryUrl(
            $attachment->storage_key,
            now()->addMinutes(5)
        );
    }
}