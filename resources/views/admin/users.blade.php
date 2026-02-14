@extends('layouts.app')

@section('title', 'Управление пользователями')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-normal">Управление пользователями</h2>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-normal">Список пользователей</h5>
        <span class="badge bg-light text-secondary border">Всего: {{ $users->total() }}</span>
    </div>
    <div class="card-body">
        
        @if($users->isEmpty())
            <div class="bg-light p-4 text-center text-secondary">
                Нет пользователей
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-normal">ID</th>
                            <th class="fw-normal">Имя</th>
                            <th class="fw-normal">Email</th>
                            <th class="fw-normal">Роль</th>
                            <th class="fw-normal">Работ</th>
                            <th class="fw-normal">Дата регистрации</th>
                            <th class="fw-normal">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td class="text-secondary">{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td class="text-secondary">{{ $user->email }}</td>
                                <td>
                                    @switch($user->role)
                                        @case('admin')
                                            <span class="badge bg-light text-secondary border">Администратор</span>
                                            @break
                                        @case('jury')
                                            <span class="badge bg-light text-secondary border">Жюри</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-secondary border">Участник</span>
                                    @endswitch
                                </td>
                                <td><span class="badge bg-light text-secondary border">{{ $user->submissions_count }}</span></td>
                                <td class="text-secondary">{{ $user->created_at->format('d.m.Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        
                                        <!-- Кнопка изменения роли -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-secondary rounded-0"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#changeRoleModal{{ $user->id }}"
                                                title="Изменить роль">
                                            Изменить роль
                                        </button>
                                        
                                        @if($user->id !== auth()->id() && $user->submissions_count == 0)
                                            <form method="POST" 
                                                  action="{{ route('admin.users.delete', $user) }}" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?')">
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
                                                    title="Нельзя удалить этого пользователя">
                                                Удалить
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            
                            <div class="modal fade" id="changeRoleModal{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content rounded-0">
                                        <form method="POST" action="{{ route('admin.users.role', $user) }}">
                                            @csrf
                                            <div class="modal-header border-0">
                                                <h5 class="modal-title fw-normal">Изменить роль пользователя</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="text-secondary">Пользователь: <span class="text-dark">{{ $user->name }}</span></p>
                                                <div class="mb-3">
                                                    <label class="form-label text-secondary">Выберите роль</label>
                                                    <select name="role" class="form-select rounded-0">
                                                        <option value="participant" {{ $user->role == 'participant' ? 'selected' : '' }}>Участник</option>
                                                        <option value="jury" {{ $user->role == 'jury' ? 'selected' : '' }}>Жюри</option>
                                                        <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Администратор</option>
                                                    </select>
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
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection