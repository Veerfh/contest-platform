<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    protected $fillable = [
        'submission_id',
        'user_id',
        'original_name',
        'mime',
        'size',
        'storage_key',
        'status',
        'rejection_reason'
    ];

    protected $casts = [
        'size' => 'integer'
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_SCANNED = 'scanned';
    public const STATUS_REJECTED = 'rejected';

    public const ALLOWED_MIMES = [
        'application/pdf',
        'application/zip',
        'image/png',
        'image/jpeg'
    ];

    public const MAX_SIZE = 10485760; // 10MB
    public const MAX_FILES_PER_SUBMISSION = 3;

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}