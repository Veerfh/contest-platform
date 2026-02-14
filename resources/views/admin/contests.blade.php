@extends('layouts.app')

@section('title', 'Управление конкурсами')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-normal">Управление конкурсами</h2>
    <a href="{{ route('admin.contests.create') }}" class="btn btn-outline-secondary rounded-0">
        <i class="fas fa-plus me-2"></i>Новый конкурс
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-normal">Список конкурсов</h5>
        <span class="badge bg-light text-secondary border">Всего: {{ $contests->total() }}</span>
    </div>
    <div class="card-body">
        
        @if($contests->isEmpty())
            <div class="bg-light p-4 text-center text-secondary">
                Нет конкурсов
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-normal">ID</th>
                            <th class="fw-normal">Название</th>
                            <th class="fw-normal">Описание</th>
                            <th class="fw-normal">Дедлайн</th>
                            <th class="fw-normal">Статус</th>
                            <th class="fw-normal">Работ</th>
                            <th class="fw-normal">Создан</th>
                            <th class="fw-normal">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contests as $contest)
                            <tr>
                                <td class="text-secondary">{{ $contest->id }}</td>
                                <td>{{ $contest->title }}</td>
                                <td class="text-secondary">{{ Str::limit($contest->description, 50) }}</td>
                                <td>
                                    <span class="text-secondary">{{ $contest->deadline_at->format('d.m.Y H:i') }}</span>
                                    @if($contest->deadline_at < now())
                                        <span class="badge bg-light text-secondary border ms-1">Просрочен</span>
                                    @endif
                                </td>
                                <td>
                                    @if($contest->is_active)
                                        <span class="badge bg-light text-secondary border">Активен</span>
                                    @else
                                        <span class="badge bg-light text-secondary border">Неактивен</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-light text-secondary border">{{ $contest->submissions_count }}</span></td>
                                <td class="text-secondary">{{ $contest->created_at->format('d.m.Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.contests.edit', $contest) }}" 
                                           class="btn btn-sm btn-outline-secondary rounded-0" 
                                           title="Редактировать">
                                            Редактировать
                                        </a>
                                        
                                        <form method="POST" 
                                              action="{{ route('admin.contests.toggle', $contest) }}" 
                                              class="d-inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-secondary rounded-0"
                                                    title="{{ $contest->is_active ? 'Деактивировать' : 'Активировать' }}">
                                                {{ $contest->is_active ? 'Деактивировать' : 'Активировать' }}
                                            </button>
                                        </form>
                                        
                                        @if($contest->submissions_count == 0)
                                            <form method="POST" 
                                                  action="{{ route('admin.contests.delete', $contest) }}" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Вы уверены, что хотите удалить этот конкурс?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-secondary rounded-0"
                                                        title="Удалить">
                                                    Удалить
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary rounded-0" 
                                                    disabled
                                                    title="Нельзя удалить конкурс с работами">
                                                Удалить
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $contests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection