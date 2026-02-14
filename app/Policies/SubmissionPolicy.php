<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Submission;

class SubmissionPolicy
{
    public function view(User $user, Submission $submission): bool
    {
        return $user->isJury() || $user->isAdmin() || $user->id === $submission->user_id;
    }
    
    public function create(User $user): bool
    {
        return $user->isParticipant();
    }
    
    public function update(User $user, Submission $submission): bool
    {
        return $user->id === $submission->user_id && $submission->isEditable();
    }
}