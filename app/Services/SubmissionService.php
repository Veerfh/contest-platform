<?php

namespace App\Services;

use App\Models\Submission;
use App\Models\SubmissionComment;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

class SubmissionService
{
    public function create(array $data, int $userId): Submission
    {
        return DB::transaction(function () use ($data, $userId) {
            $submission = Submission::create([
                'contest_id' => $data['contest_id'],
                'user_id' => $userId,
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => Submission::STATUS_DRAFT,
            ]);

            return $submission;
        });
    }

    public function update(Submission $submission, array $data): Submission
    {
        if (!$submission->isEditable()) {
            throw new \Exception('Submission cannot be edited in current status.');
        }

        $submission->update($data);
        return $submission;
    }

    public function submit(Submission $submission): Submission
    {
        if (!$submission->isEditable()) {
            throw new \Exception('Only draft or needs_fix submissions can be submitted.');
        }

        if (!$submission->hasScannedAttachments()) {
            throw new \Exception('Submission must have at least one scanned attachment.');
        }

        $submission->status = Submission::STATUS_SUBMITTED;
        $submission->save();

        return $submission;
    }

    public function changeStatus(Submission $submission, string $newStatus, ?string $comment = null): Submission
    {
        $allowedTransitions = Submission::getAllowedStatusTransitions();
        
        if (!in_array($newStatus, $allowedTransitions[$submission->status] ?? [])) {
            throw new \Exception('Invalid status transition.');
        }

        $oldStatus = $submission->status;
        $submission->status = $newStatus;
        $submission->save();

        if ($comment) {
            $this->addComment($submission, $comment, auth()->id());
        }

        return $submission;
    }

    public function addComment(Submission $submission, string $body, int $userId): SubmissionComment
    {
        return SubmissionComment::create([
            'submission_id' => $submission->id,
            'user_id' => $userId,
            'body' => $body,
        ]);
    }
}