<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Contest;
use App\Services\SubmissionService;
use App\Http\Requests\StoreSubmissionRequest;
use App\Http\Requests\UpdateSubmissionRequest;
use App\Http\Requests\ChangeSubmissionStatusRequest;
use App\Http\Requests\AddCommentRequest;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    private SubmissionService $submissionService;
    
    public function __construct(SubmissionService $submissionService)
    {
        $this->submissionService = $submissionService;
    }
    
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Submission::with(['contest', 'user', 'attachments', 'comments']);
        
        if ($user->isParticipant()) {
            $query->where('user_id', $user->id);
        }
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->contest_id) {
            $query->where('contest_id', $request->contest_id);
        }
        
        if ($request->author && ($user->isJury() || $user->isAdmin())) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->author}%")
                  ->orWhere('email', 'like', "%{$request->author}%");
            });
        }
        
        $submissions = $query->orderBy('created_at', 'desc')->paginate(10);
        $contests = Contest::where('is_active', true)->get();
        
        return view('submissions.index', compact('submissions', 'contests'));
    }
    
    public function create(Request $request)
    {
        $contests = Contest::where('is_active', true)
            ->where('deadline_at', '>', now())
            ->get();
            
        return view('submissions.form', [
            'contests' => $contests,
            'contest_id' => $request->contest_id
        ]);
    }
    
    public function store(StoreSubmissionRequest $request)
    {
        $submission = $this->submissionService->create(
            $request->validated(),
            auth()->id()
        );
        
        return redirect()
            ->route('submissions.show', $submission)
            ->with('success', 'Работа успешно создана');
    }
    
    public function show(Submission $submission)
    {
        $user = auth()->user();
        
        if (!$user->isJury() && !$user->isAdmin() && $user->id !== $submission->user_id) {
            abort(403, 'Доступ запрещен');
        }
        
        return view('submissions.show', [
            'submission' => $submission->load(['user', 'contest', 'attachments', 'comments.user'])
        ]);
    }
    
    public function edit(Submission $submission)
    {
        $user = auth()->user();

        if ($user->id !== $submission->user_id) {
            abort(403, 'Доступ запрещен');
        }
        
        if (!$submission->isEditable()) {
            return redirect()
                ->route('submissions.show', $submission)
                ->with('error', 'Нельзя редактировать работу в текущем статусе');
        }
        
        $contests = Contest::where('is_active', true)->get();
        
        return view('submissions.form', [
            'submission' => $submission,
            'contests' => $contests
        ]);
    }
    
    public function update(UpdateSubmissionRequest $request, Submission $submission)
    {
        $submission = $this->submissionService->update(
            $submission,
            $request->validated()
        );
        
        return redirect()
            ->route('submissions.show', $submission)
            ->with('success', 'Работа успешно обновлена');
    }
    
    public function submit(Submission $submission)
    {
        try {
            $submission = $this->submissionService->submit($submission);
            return redirect()
                ->route('submissions.show', $submission)
                ->with('success', 'Работа отправлена на проверку');
        } catch (\Exception $e) {
            return redirect()
                ->route('submissions.show', $submission)
                ->with('error', $e->getMessage());
        }
    }
    
    public function changeStatus(ChangeSubmissionStatusRequest $request, Submission $submission)
    {
        try {
            $submission = $this->submissionService->changeStatus(
                $submission,
                $request->status,
                $request->comment
            );
            
            return redirect()
                ->route('submissions.show', $submission)
                ->with('success', 'Статус работы изменен');
        } catch (\Exception $e) {
            return redirect()
                ->route('submissions.show', $submission)
                ->with('error', $e->getMessage());
        }
    }
    
    public function addComment(AddCommentRequest $request, Submission $submission)
    {
        $this->submissionService->addComment(
            $submission,
            $request->body,
            auth()->id()
        );
        
        return redirect()
            ->route('submissions.show', $submission)
            ->with('success', 'Комментарий добавлен');
    }
}