<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\SubmissionController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\Web\JuryController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');

Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
Route::get('/submissions/create', [SubmissionController::class, 'create'])->name('submissions.create');
Route::post('/submissions', [SubmissionController::class, 'store'])->name('submissions.store');
Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
Route::get('/submissions/{submission}/edit', [SubmissionController::class, 'edit'])->name('submissions.edit');
Route::put('/submissions/{submission}', [SubmissionController::class, 'update'])->name('submissions.update');
Route::post('/submissions/{submission}/submit', [SubmissionController::class, 'submit'])->name('submissions.submit');
Route::post('/submissions/{submission}/change-status', [SubmissionController::class, 'changeStatus'])->name('submissions.change-status');
Route::post('/submissions/{submission}/comments', [SubmissionController::class, 'addComment'])->name('submissions.comments');

Route::post('/submissions/{submission}/attachments', [AttachmentController::class, 'upload'])->name('attachments.upload');
Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');

Route::get('/jury', [JuryController::class, 'index'])->name('jury.submissions');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/contests', [AdminController::class, 'contests'])->name('contests');
    Route::get('/contests/create', [AdminController::class, 'createContest'])->name('contests.create');
    Route::post('/contests', [AdminController::class, 'storeContest'])->name('contests.store');
    Route::get('/contests/{contest}/edit', [AdminController::class, 'editContest'])->name('contests.edit');
    Route::put('/contests/{contest}', [AdminController::class, 'updateContest'])->name('contests.update');
    Route::post('/contests/{contest}/toggle', [AdminController::class, 'toggleContest'])->name('contests.toggle');
    Route::delete('/contests/{contest}', [AdminController::class, 'deleteContest'])->name('contests.delete');
    
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{user}/role', [AdminController::class, 'changeUserRole'])->name('users.role');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
});

Route::get('/notifications', [App\Http\Controllers\Web\NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifications/{notification}/read', [App\Http\Controllers\Web\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
Route::post('/notifications/read-all', [App\Http\Controllers\Web\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
Route::get('/notifications/unread-count', [App\Http\Controllers\Web\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');