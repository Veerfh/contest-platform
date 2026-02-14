@extends('layouts.app')

@section('title', $submission->title)

@section('content')
<div class="mb-4">
    <a href="{{ route('submissions.index') }}" class="btn btn-outline-secondary rounded-0">
        <i class="fas fa-arrow-left me-2"></i>Назад к списку
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h4 class="mb-0 fw-normal">{{ $submission->title }}</h4>
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
            
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-1 text-secondary">Конкурс: {{ $submission->contest->title }}</p>
                        <p class="mb-1 text-secondary">Автор: {{ $submission->user->name }}</p>
                        <p class="mb-1 text-secondary">Email: {{ $submission->user->email }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 text-secondary">Создано: {{ $submission->created_at->format('d.m.Y H:i') }}</p>
                        <p class="mb-1 text-secondary">Обновлено: {{ $submission->updated_at->format('d.m.Y H:i') }}</p>
                    </div>
                </div>
                
                <h5 class="fw-normal">Описание</h5>
                <div class="p-3 bg-light rounded-0">
                    {{ $submission->description }}
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-normal">Файлы ({{ $submission->attachments->count() }}/3)</h5>
            </div>
            <div class="card-body">
                @if($submission->attachments->isEmpty())
                    <p class="text-secondary mb-0">Нет загруженных файлов</p>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-normal">Имя файла</th>
                                    <th class="fw-normal">Размер</th>
                                    <th class="fw-normal">Статус</th>
                                    <th class="fw-normal">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($submission->attachments as $attachment)
                                    <tr>
                                        <td>
                                            {{ $attachment->original_name }}
                                        </td>
                                        <td class="text-secondary">{{ round($attachment->size / 1024, 2) }} KB</td>
                                        <td>
                                            @if($attachment->status == 'scanned')
                                                <span class="badge bg-light text-secondary border">Проверен</span>
                                            @elseif($attachment->status == 'rejected')
                                                <span class="badge bg-light text-secondary border" title="{{ $attachment->rejection_reason }}">
                                                    Отклонен
                                                </span>
                                            @else
                                                <span class="badge bg-light text-secondary border">В очереди на проверку</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attachment->status == 'scanned')
                                                <a href="{{ route('attachments.download', $attachment) }}" 
                                                   class="btn btn-sm btn-outline-secondary rounded-0" 
                                                   target="_blank">
                                                    Скачать
                                                </a>
                                            @else
                                                <button class="btn btn-sm btn-outline-secondary rounded-0" disabled>
                                                    Скачать
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($attachment->status == 'rejected' && $attachment->rejection_reason)
                                        <tr class="bg-light">
                                            <td colspan="4" class="small text-secondary py-2">
                                                Причина отклонения: {{ $attachment->rejection_reason }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
                
                @if($submission->isEditable() && auth()->id() === $submission->user_id && $submission->attachments->count() < 3)
                    <hr class="text-secondary">
                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-secondary">Загрузить новый файл</label>
                            <input type="file" class="form-control rounded-0" id="fileInput" name="file" 
                                   accept=".pdf,.zip,.png,.jpg,.jpeg">
                            <small class="text-secondary">
                                Максимальный размер: 10MB. Разрешенные форматы: PDF, ZIP, PNG, JPG
                            </small>
                        </div>
                        <button type="button" class="btn btn-outline-secondary rounded-0" onclick="uploadFile()">
                            Загрузить
                        </button>
                    </form>
                @endif
            </div>
        </div>
        
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-normal">Комментарии ({{ $submission->comments->count() }})</h5>
            </div>
            <div class="card-body">
                @if($submission->comments->isEmpty())
                    <p class="text-secondary">Нет комментариев</p>
                @else
                    @foreach($submission->comments as $comment)
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <div class="bg-light text-secondary rounded-circle p-3 me-2">
                                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="bg-light p-3 rounded-0">
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong class="fw-normal">{{ $comment->user->name }}</strong>
                                        <small class="text-secondary">{{ $comment->created_at->diffForHumans() }}</small>
                                    </div>
                                    <p class="mb-0 text-secondary">{{ $comment->body }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
                
                <hr class="text-secondary">
                
                <form method="POST" action="{{ route('submissions.comments', $submission) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-secondary">Добавить комментарий</label>
                        <textarea name="body" class="form-control rounded-0" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-secondary rounded-0">
                        Отправить
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        @if(auth()->user()->isJury() && $submission->status == 'submitted')
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-normal">Действия жюри</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('submissions.change-status', $submission) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-secondary">Изменить статус</label>
                            <select name="status" class="form-select rounded-0 mb-2">
                                <option value="accepted">Принять работу</option>
                                <option value="needs_fix">Требует доработки</option>
                                <option value="rejected">Отклонить</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary">Комментарий</label>
                            <textarea name="comment" class="form-control rounded-0" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-outline-secondary w-100 rounded-0">
                            Применить
                        </button>
                    </form>
                </div>
            </div>
        @endif
        
        @if($submission->isEditable() && auth()->id() === $submission->user_id)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-normal">Действия</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('submissions.edit', $submission) }}" class="btn btn-outline-secondary w-100 rounded-0 mb-2">
                        Редактировать
                    </a>
                    
                    @if($submission->hasScannedAttachments())
                        <form method="POST" action="{{ route('submissions.submit', $submission) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary w-100 rounded-0">
                                Отправить на проверку
                            </button>
                        </form>
                    @else
                        <button class="btn btn-outline-secondary w-100 rounded-0" disabled>
                            Отправить на проверку
                        </button>
                        <small class="text-secondary d-block mt-2">
                            Дождитесь проверки загруженных файлов
                        </small>
                    @endif
                </div>
            </div>
        @endif
        
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-normal">Информация</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2 text-secondary">
                        <strong>Статус:</strong> 
                        @switch($submission->status)
                            @case('draft') Черновик @break
                            @case('submitted') На проверке @break
                            @case('needs_fix') Требует доработки @break
                            @case('accepted') Принято @break
                            @case('rejected') Отклонено @break
                        @endswitch
                    </li>
                    <li class="mb-2 text-secondary">
                        <strong>Файлы:</strong> {{ $submission->attachments->count() }}/3
                    </li>
                    <li class="mb-2 text-secondary">
                        <strong>Комментарии:</strong> {{ $submission->comments->count() }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function uploadFile() {
    const fileInput = document.getElementById('fileInput');
    
    if (!fileInput.files || fileInput.files.length === 0) {
        alert('Пожалуйста, выберите файл');
        return;
    }
    
    const file = fileInput.files[0];
    
    const maxSize = 10 * 1024 * 1024; // 10MB
    if (file.size > maxSize) {
        const fileSizeMB = (file.size / 1048576).toFixed(2);
        alert(`Файл слишком большой. Максимальный размер: 10MB, текущий размер: ${fileSizeMB}MB`);
        return;
    }
    
    const allowedExtensions = ['pdf', 'zip', 'png', 'jpg', 'jpeg'];
    const fileExtension = file.name.split('.').pop().toLowerCase();
    
    if (!allowedExtensions.includes(fileExtension)) {
        alert(`Недопустимый тип файла. Разрешенные форматы: PDF, ZIP, PNG, JPG (получено: .${fileExtension})`);
        return;
    }
    
    const formData = new FormData();
    formData.append('file', file);
    
    const uploadBtn = event.target;
    const originalText = uploadBtn.innerHTML;
    uploadBtn.innerHTML = 'Загрузка...';
    uploadBtn.disabled = true;
    
    const errorDiv = document.getElementById('uploadError');
    if (errorDiv) errorDiv.remove();
    
    fetch('{{ route("attachments.upload", $submission) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json().then(data => ({ status: response.status, body: data })))
    .then(result => {
        if (result.status === 201 || result.status === 200) {
            location.reload();
        } else {
            let errorMessage = 'Ошибка при загрузке файла';
            
            if (result.body && result.body.error) {
                errorMessage = result.body.error;
            } else if (result.body && result.body.message) {
                errorMessage = result.body.message;
            }
            
            const form = document.getElementById('uploadForm');
            const errorDiv = document.createElement('div');
            errorDiv.id = 'uploadError';
            errorDiv.className = 'alert alert-light text-secondary border mt-3 rounded-0';
            errorDiv.innerHTML = errorMessage;
            form.appendChild(errorDiv);
            
            console.error('Upload error:', result.body);
        }
    })
    .catch(error => {
        console.error('Network error:', error);
        
        const form = document.getElementById('uploadForm');
        const errorDiv = document.createElement('div');
        errorDiv.id = 'uploadError';
        errorDiv.className = 'alert alert-light text-secondary border mt-3 rounded-0';
        errorDiv.innerHTML = 'Ошибка соединения с сервером. Проверьте подключение.';
        form.appendChild(errorDiv);
    })
    .finally(() => {
        uploadBtn.innerHTML = originalText;
        uploadBtn.disabled = false;
    });
}
</script>
@endpush
@endsection