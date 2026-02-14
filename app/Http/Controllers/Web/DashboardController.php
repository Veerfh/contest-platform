<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Contest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $stats = [];
        $recentSubmissions = [];
        $activeContests = Contest::where('is_active', true)
            ->where('deadline_at', '>', now())
            ->orderBy('deadline_at')
            ->limit(3)
            ->get();
        
        if ($user->isParticipant()) {
            $submissions = $user->submissions();
            $stats = [
                'total' => $submissions->count(),
                'draft' => (clone $submissions)->where('status', Submission::STATUS_DRAFT)->count(),
                'submitted' => (clone $submissions)->where('status', Submission::STATUS_SUBMITTED)->count(),
                'accepted' => (clone $submissions)->where('status', Submission::STATUS_ACCEPTED)->count(),
            ];
            $recentSubmissions = $user->submissions()
                ->with(['contest', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        } else {
            $stats = [
                'total' => Submission::count(),
                'submitted' => Submission::where('status', Submission::STATUS_SUBMITTED)->count(),
                'accepted' => Submission::where('status', Submission::STATUS_ACCEPTED)->count(),
            ];
            $recentSubmissions = Submission::with(['contest', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }
        
        return view('dashboard', compact('stats', 'recentSubmissions', 'activeContests'));
    }
    
    public function profile()
    {
        return view('profile');
    }
}