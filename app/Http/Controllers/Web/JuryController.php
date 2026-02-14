<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Http\Request;

class JuryController extends Controller
{
    /**
     * Display a listing of submissions for jury.
     */
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $user = auth()->user();
        if (!$user->isJury() && !$user->isAdmin()) {
            abort(403, 'Доступ только для членов жюри');
        }
        
        $submissions = Submission::with(['user', 'contest', 'attachments'])
            ->where('status', Submission::STATUS_SUBMITTED)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('jury.index', compact('submissions'));
    }
}