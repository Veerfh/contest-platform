@extends('layouts.app')

@section('title', 'Дашборд')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-normal">
        @if(auth()->user()->isAdmin())
            Панель администратора
        @elseif(auth()->user()->isJury())
            Панель жюри
        @else
            Мои работы
        @endif
    </h2>
    
    @if(auth()->user()->isParticipant())
        <a href="{{ route('submissions.create') }}" class="btn btn-outline-secondary rounded-0">
            <i class="fas fa-plus me-2"></i>Новая работа
        </a>
    @endif
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-normal">Активные конкурсы</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @forelse($activeContests ?? [] as $contest)
                <div class="col-md-4">
                    <div class="card h-100 border-0 bg-light">
                        <div class="card-body">
                            <h5 class="card-title h6">{{ $contest->title }}</h5>
                            <p class="card-text small text-secondary">{{ Str::limit($contest->description, 100) }}</p>
                            <p class="small text-secondary mb-0">
                                Дедлайн: {{ $contest->deadline_at->format('d.m.Y H:i') }}
                            </p>
                        </div>
                        @if(auth()->user()->isParticipant())
                        <div class="card-footer bg-white border-0">
                            <a href="{{ route('submissions.create', ['contest_id' => $contest->id]) }}" 
                               class="btn btn-sm btn-outline-secondary rounded-0">
                                <i class="fas fa-plus me-1"></i>Создать работу
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-secondary mb-0">Нет активных конкурсов</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Последние работы -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-normal">Последние работы</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="fw-normal">Название</th>
                        @if(auth()->user()->isJury() || auth()->user()->isAdmin())
                            <th class="fw-normal">Автор</th>
                        @endif
                        <th class="fw-normal">Конкурс</th>
                        <th class="fw-normal">Статус</th>
                        <th class="fw-normal">Дата</th>
                        <th class="fw-normal"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSubmissions ?? [] as $submission)
                        <tr>
                            <td>{{ $submission->title }}</td>
                            @if(auth()->user()->isJury() || auth()->user()->isAdmin())
                                <td>{{ $submission->user->name }}</td>
                            @endif
                            <td>{{ $submission->contest->title }}</td>
                            <td>
                                <span class="badge bg-light text-secondary fw-normal px-3 py-2">
                                    @switch($submission->status)
                                        @case('draft') Черновик @break
                                        @case('submitted') На проверке @break
                                        @case('needs_fix') Требует доработки @break
                                        @case('accepted') Принято @break
                                        @case('rejected') Отклонено @break
                                    @endswitch
                                </span>
                            </td>
                            <td class="text-secondary">{{ $submission->created_at->format('d.m.Y') }}</td>
                            <td>
                                <a href="{{ route('submissions.show', $submission) }}" 
                                   class="btn btn-sm btn-outline-secondary rounded-0">
                                    Просмотр
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">
                                Нет работ
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection