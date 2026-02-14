<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Services\SubmissionService;
use App\Http\Requests\StoreSubmissionRequest;
use App\Http\Requests\UpdateSubmissionRequest;
use App\Http\Requests\ChangeSubmissionStatusRequest;
use App\Http\Requests\AddCommentRequest;
use Illuminate\Http\JsonResponse;

class SubmissionController extends Controller
{
    private SubmissionService $submissionService;

    public function __construct(SubmissionService $submissionService)
    {
        $this->submissionService = $submissionService;
    }

    public function index(): JsonResponse
    {
        $user = auth()->user();
        
        if ($user->isJury() || $user->isAdmin()) {
            $submissions = Submission::with(['user', 'contest', 'attachments'])->get();
        } else {
            $submissions = $user->submissions()->with(['contest', 'attachments'])->get();
        }

        return response()->json($submissions);
    }

    public function store(StoreSubmissionRequest $request): JsonResponse
    {
        $submission = $this->submissionService->create(
            $request->validated(),
            auth()->id()
        );

        return response()->json($submission, 201);
    }

    public function show(Submission $submission): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user->isJury() && !$user->isAdmin() && $user->id !== $submission->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($submission->load(['user', 'contest', 'attachments', 'comments.user']));
    }

    public function update(UpdateSubmissionRequest $request, Submission $submission): JsonResponse
    {
        $submission = $this->submissionService->update(
            $submission,
            $request->validated()
        );

        return response()->json($submission);
    }

    public function submit(Submission $submission): JsonResponse
    {
        try {
            $submission = $this->submissionService->submit($submission);
            return response()->json($submission);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function changeStatus(ChangeSubmissionStatusRequest $request, Submission $submission): JsonResponse
    {
        try {
            $submission = $this->submissionService->changeStatus(
                $submission,
                $request->status,
                $request->comment
            );
            return response()->json($submission);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function addComment(AddCommentRequest $request, Submission $submission): JsonResponse
    {
        $comment = $this->submissionService->addComment(
            $submission,
            $request->body,
            auth()->id()
        );

        return response()->json($comment->load('user'), 201);
    }
}