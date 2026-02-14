<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Jobs\NotifyStatusChangedJob;

class Submission extends Model
{
    protected $fillable = [
        'contest_id',
        'user_id',
        'title',
        'description',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_NEEDS_FIX = 'needs_fix';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    public static function getAllowedStatusTransitions(): array
    {
        return [
            self::STATUS_DRAFT => [self::STATUS_SUBMITTED],
            self::STATUS_SUBMITTED => [self::STATUS_NEEDS_FIX, self::STATUS_ACCEPTED, self::STATUS_REJECTED],
            self::STATUS_NEEDS_FIX => [self::STATUS_SUBMITTED, self::STATUS_REJECTED],
            self::STATUS_ACCEPTED => [],
            self::STATUS_REJECTED => [],
        ];
    }

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(SubmissionComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_NEEDS_FIX]);
    }

    public function hasScannedAttachments(): bool
    {
        return $this->attachments()->where('status', 'scanned')->exists();
    }

    protected static function booted()
    {
        static::updated(function ($submission) {
            if ($submission->isDirty('status')) {
                $oldStatus = $submission->getOriginal('status');
                $newStatus = $submission->status;
                
                dispatch(new NotifyStatusChangedJob($submission, $oldStatus, $newStatus));
            }
        });
    }
}