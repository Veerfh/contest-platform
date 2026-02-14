@extends('layouts.app')

@section('title', 'Панель жюри')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-normal">Панель жюри</h2>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-normal">Работы на проверке</h5>
    </div>
    <div class="card-body">
        @if($submissions->isEmpty())
            <div class="bg-light p-4 text-center text-secondary">
                Нет работ на проверке
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-normal">ID</th>
                            <th class="fw-normal">Название</th>
                            <th class="fw-normal">Автор</th>
                            <th class="fw-normal">Конкурс</th>
                            <th class="fw-normal">Файлы</th>
                            <th class="fw-normal">Дата подачи</th>
                            <th class="fw-normal">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($submissions as $submission)
                            <tr>
                                <td class="text-secondary">{{ $submission->id }}</td>
                                <td>{{ $submission->title }}</td>
                                <td>{{ $submission->user->name }}</td>
                                <td>{{ $submission->contest->title }}</td>
                                <td>
                                    <span class="badge bg-light text-secondary border me-2">{{ $submission->attachments->count() }}/3</span>
                                    @foreach($submission->attachments as $attachment)
                                        @if($attachment->status == 'scanned')
                                            <span class="badge bg-light text-secondary border" title="Проверен">✓</span>
                                        @elseif($attachment->status == 'pending')
                                            <span class="badge bg-light text-secondary border" title="В очереди">⌛</span>
                                        @elseif($attachment->status == 'rejected')
                                            <span class="badge bg-light text-secondary border" title="Отклонен">⚠</span>
                                        @endif
                                    @endforeach
                                </td>
                                <td class="text-secondary">{{ $submission->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('submissions.show', $submission) }}" class="btn btn-sm btn-outline-secondary rounded-0">
                                        Просмотр
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $submissions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection