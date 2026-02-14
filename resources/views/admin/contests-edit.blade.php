@extends('layouts.app')

@section('title', 'Редактирование конкурса')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h4 class="mb-0 fw-normal">Редактирование конкурса</h4>
            </div>
            
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.contests.update', $contest) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="title" class="form-label text-secondary">Название конкурса</label>
                        <input type="text" 
                               class="form-control rounded-0 @error('title') is-invalid @enderror" 
                               id="title" 
                               name="title" 
                               value="{{ old('title', $contest->title) }}" 
                               required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label text-secondary">Описание</label>
                        <textarea class="form-control rounded-0 @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="5" 
                                  required>{{ old('description', $contest->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="deadline_at" class="form-label text-secondary">Дедлайн</label>
                        <input type="datetime-local" 
                               class="form-control rounded-0 @error('deadline_at') is-invalid @enderror" 
                               id="deadline_at" 
                               name="deadline_at" 
                               value="{{ old('deadline_at', $contest->deadline_at->format('Y-m-d\TH:i')) }}" 
                               required>
                        <small class="text-secondary">Дата и время окончания приема работ</small>
                        @error('deadline_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="is_active" 
                               name="is_active" 
                               {{ old('is_active', $contest->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label text-secondary" for="is_active">
                            Активен (доступен для подачи работ)
                        </label>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.contests') }}" class="btn btn-outline-secondary rounded-0">
                            <i class="fas fa-arrow-left me-2"></i>Назад
                        </a>
                        
                        <button type="submit" class="btn btn-outline-secondary rounded-0">
                            Сохранить изменения
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection