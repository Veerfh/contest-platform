@extends('layouts.app')

@section('title', isset($submission) ? 'Редактировать работу' : 'Новая работа')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h4 class="mb-0 fw-normal">
                    {{ isset($submission) ? 'Редактировать работу' : 'Новая работа' }}
                </h4>
            </div>
            
            <div class="card-body p-4">
                <form method="POST" action="{{ isset($submission) ? route('submissions.update', $submission) : route('submissions.store') }}">
                    @csrf
                    @if(isset($submission))
                        @method('PUT')
                    @endif
                    
                    <div class="mb-3">
                        <label for="contest_id" class="form-label text-secondary">Конкурс</label>
                        <select class="form-select rounded-0 @error('contest_id') is-invalid @enderror" 
                                id="contest_id" name="contest_id" {{ isset($submission) ? 'disabled' : '' }}>
                            <option value="">Выберите конкурс</option>
                            @foreach($contests as $contest)
                                <option value="{{ $contest->id }}" 
                                    {{ (old('contest_id', isset($submission) ? $submission->contest_id : request('contest_id')) == $contest->id) ? 'selected' : '' }}>
                                    {{ $contest->title }} (до {{ $contest->deadline_at->format('d.m.Y') }})
                                </option>
                            @endforeach
                        </select>
                        @error('contest_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        
                        @if(isset($submission))
                            <input type="hidden" name="contest_id" value="{{ $submission->contest_id }}">
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label text-secondary">Название работы</label>
                        <input type="text" class="form-control rounded-0 @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title', isset($submission) ? $submission->title : '') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label text-secondary">Описание</label>
                        <textarea class="form-control rounded-0 @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="5" required>{{ old('description', isset($submission) ? $submission->description : '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('submissions.index') }}" class="btn btn-outline-secondary rounded-0">
                            <i class="fas fa-arrow-left me-2"></i>Назад
                        </a>
                        
                        <div>
                            @if(isset($submission) && $submission->isEditable())
                                <button type="button" class="btn btn-outline-secondary rounded-0 me-2" onclick="submitAndRedirect({{ $submission->id }})">
                                    Отправить на проверку
                                </button>
                            @endif
                            
                            <button type="submit" class="btn btn-outline-secondary rounded-0">
                                {{ isset($submission) ? 'Сохранить' : 'Создать' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function submitAndRedirect(submissionId) {
    if (confirm('Отправить работу на проверку?')) {
        fetch('/submissions/' + submissionId + '/submit', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                window.location.href = '/submissions/' + submissionId;
            } else {
                alert('Ошибка при отправке на проверку');
            }
        })
        .catch(error => {
            alert('Ошибка при отправке на проверку');
        });
    }
}
</script>
@endpush
@endsection