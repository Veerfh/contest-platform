<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\AttachmentController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('submissions', SubmissionController::class)->except(['destroy']);
    Route::post('submissions/{submission}/submit', [SubmissionController::class, 'submit']);
    Route::post('submissions/{submission}/change-status', [SubmissionController::class, 'changeStatus']);
    Route::post('submissions/{submission}/comments', [SubmissionController::class, 'addComment']);
    
    Route::post('submissions/{submission}/attachments', [AttachmentController::class, 'upload']);
    Route::get('attachments/{attachment}/download', [AttachmentController::class, 'download']);
});