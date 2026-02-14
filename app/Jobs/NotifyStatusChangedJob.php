<?php

namespace App\Jobs;

use App\Models\Submission;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyStatusChangedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Submission $submission;
    protected string $oldStatus;
    protected string $newStatus;

    public function __construct(Submission $submission, string $oldStatus, string $newStatus)
    {
        $this->submission = $submission;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function handle(): void
    {
        $message = "Status of submission '{$this->submission->title}' changed from {$this->oldStatus} to {$this->newStatus}.";
        
        Notification::create([
            'user_id' => $this->submission->user_id,
            'type' => 'submission_status_changed',
            'message' => $message,
            'is_read' => false,
        ]);
    }
}