@extends('layouts.app')

@section('title', 'Работы')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-normal">Работы</h2>
    
    @if(auth()->user()->isParticipant())
        <a href="{{ route('submissions.create') }}" class="btn btn-outline-secondary rounded-0">
            <i class="fas fa-plus me-2"></i>Новая работа
        </a>
    @endif
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label text-secondary">Статус</label>
                <select name="status" class="form-select rounded-0">
                    <option value="">Все статусы</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Черновик</option>
                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>На проверке</option>
                    <option value="needs_fix" {{ request('status') == 'needs_fix' ? 'selected' : '' }}>Требует доработки</option>
                    <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Принято</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Отклонено</option>
                </select>
            </div>
            
            @if(auth()->user()->isJury() || auth()->user()->isAdmin())
            <div class="col-md-4">
                <label class="form-label text-secondary">Автор</label>
                <input type="text" name="author" class="form-control rounded-0" value="{{ request('author') }}" placeholder="Имя или email">
            </div>
            @endif
            
            <div class="col-md-4">
                <label class="form-label text-secondary">Конкурс</label>
                <select name="contest_id" class="form-select rounded-0">
                    <option value="">Все конкурсы</option>
                    @foreach($contests ?? [] as $contest)
                        <option value="{{ $contest->id }}" {{ request('contest_id') == $contest->id ? 'selected' : '' }}>
                            {{ $contest->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-12">
                <button type="submit" class="btn btn-outline-secondary rounded-0">
                    Применить фильтры
                </button>
                <a href="{{ route('submissions.index') }}" class="btn btn-outline-secondary rounded-0">
                    Сбросить
                </a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3">
    @forelse($submissions as $submission)
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title h6 mb-0">{{ $submission->title }}</h5>
                        <span class="badge bg-light text-secondary fw-normal px-3 py-2">
                            @switch($submission->status)
                                @case('draft') Черновик @break
                                @case('submitted') На проверке @break
                                @case('needs_fix') Требует доработки @break
                                @case('accepted') Принято @break
                                @case('rejected') Отклонено @break
                            @endswitch
                        </span>
                    </div>
                    
                    <p class="text-secondary small mb-2">
                        {{ $submission->created_at->format('d.m.Y H:i') }}
                    </p>
                    
                    <p class="card-text text-secondary">{{ Str::limit($submission->description, 150) }}</p>
                    
                    <div class="mb-2 small">
                        <span class="text-secondary">Конкурс:</span> {{ $submission->contest->title }}
                    </div>
                    
                    @if(auth()->user()->isJury() || auth()->user()->isAdmin())
                        <div class="mb-2 small">
                            <span class="text-secondary">Автор:</span> {{ $submission->user->name }}
                        </div>
                    @endif
                    
                    <div class="mb-3 small">
                        <span class="text-secondary">Файлы:</span> 
                        <span class="badge bg-light text-secondary">{{ $submission->attachments->count() }}/3</span>
                        @foreach($submission->attachments as $attachment)
                            <span class="badge bg-light text-secondary border">
                                {{ $attachment->original_name }}
                                @if($attachment->status == 'rejected')
                                    <span class="text-secondary" title="{{ $attachment->rejection_reason }}">⚠</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                    
                    @if($submission->comments->count() > 0)
                        <div class="small text-secondary">
                            Комментариев: {{ $submission->comments->count() }}
                        </div>
                    @endif
                </div>
                
                <div class="card-footer bg-white border-0">
                    <div class="btn-group">
                        <a href="{{ route('submissions.show', $submission) }}" class="btn btn-sm btn-outline-secondary rounded-0">
                            Просмотр
                        </a>
                        
                        @if($submission->isEditable() && auth()->id() === $submission->user_id)
                            <a href="{{ route('submissions.edit', $submission) }}" class="btn btn-sm btn-outline-secondary rounded-0">
                                Редактировать
                            </a>
                        @endif
                        
                        @if(auth()->user()->isJury() && $submission->status == 'submitted')
                            <button class="btn btn-sm btn-outline-secondary rounded-0" onclick="showStatusModal({{ $submission->id }})">
                                Оценить
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="bg-light p-4 text-center text-secondary">
                Нет работ для отображения
            </div>
        </div>
    @endforelse
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $submissions->links() }}
</div>

@if(auth()->user()->isJury())
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-0">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-normal">Изменить статус работы</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Новый статус</label>
                        <select name="status" class="form-select rounded-0" required>
                            <option value="accepted">Принять</option>
                            <option value="needs_fix">Требует доработки</option>
                            <option value="rejected">Отклонить</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary">Комментарий (необязательно)</label>
                        <textarea name="comment" class="form-control rounded-0" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary rounded-0" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-outline-secondary rounded-0">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showStatusModal(submissionId) {
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    const form = document.getElementById('statusForm');
    form.action = `/submissions/${submissionId}/change-status`;
    modal.show();
}
</script>
@endpush
@endif
@endsection