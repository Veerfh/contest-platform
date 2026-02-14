<?php

namespace App\Jobs;

use App\Models\Attachment;
use App\Services\AttachmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScanAttachmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Attachment $attachment;

    public function __construct(Attachment $attachment)
    {
        $this->attachment = $attachment;
    }

    public function handle(AttachmentService $attachmentService): void
    {
        try {
            $allowedMimes = [
                'application/pdf',
                'application/zip',
                'application/x-zip-compressed',
                'image/png',
                'image/jpeg',
                'image/jpg'
            ];
            
            if (!in_array($this->attachment->mime, $allowedMimes)) {
                $attachmentService->reject(
                    $this->attachment, 
                    'Недопустимый тип файла. Разрешенные форматы: PDF, ZIP, PNG, JPG (получен: ' . $this->attachment->mime . ')'
                );
                return;
            }

            $extension = pathinfo($this->attachment->original_name, PATHINFO_EXTENSION);
            $allowedExtensions = ['pdf', 'zip', 'png', 'jpg', 'jpeg'];
            
            if (!in_array(strtolower($extension), $allowedExtensions)) {
                $attachmentService->reject(
                    $this->attachment,
                    'Недопустимое расширение файла. Разрешенные: .pdf, .zip, .png, .jpg (получено: .' . $extension . ')'
                );
                return;
            }

            if ($this->attachment->size > Attachment::MAX_SIZE) {
                $sizeInMB = round($this->attachment->size / 1048576, 2);
                $maxSizeInMB = Attachment::MAX_SIZE / 1048576;
                $attachmentService->reject(
                    $this->attachment,
                    "Файл слишком большой. Максимальный размер: {$maxSizeInMB}MB, текущий размер: {$sizeInMB}MB"
                );
                return;
            }

            if (preg_match('/[^\w\s.-]/u', $this->attachment->original_name)) {
                $attachmentService->reject(
                    $this->attachment,
                    'Имя файла содержит недопустимые символы. Разрешены: буквы, цифры, пробел, точка, дефис, подчеркивание'
                );
                return;
            }

            if ($this->attachment->size === 0) {
                $attachmentService->reject(
                    $this->attachment,
                    'Файл пустой'
                );
                return;
            }

            $attachmentService->markScanned($this->attachment);
            
            Log::info('Attachment scanned successfully', [
                'attachment_id' => $this->attachment->id,
                'file_name' => $this->attachment->original_name
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error scanning attachment', [
                'attachment_id' => $this->attachment->id,
                'error' => $e->getMessage()
            ]);
            
            $attachmentService->reject(
                $this->attachment,
                'Ошибка при проверке файла: ' . $e->getMessage()
            );
        }
    }
}