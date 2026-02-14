@extends('layouts.app')

@section('title', 'Уведомления')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-normal">Уведомления</h2>
    
    @if($notifications->where('is_read', false)->count() > 0)
        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary rounded-0">
                Отметить все как прочитанные
            </button>
        </form>
    @endif
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        @if($notifications->isEmpty())
            <div class="text-center py-5">
                <p class="text-secondary mb-0">Нет уведомлений</p>
            </div>
        @else
            <div class="list-group">
                @foreach($notifications as $notification)
                    <div class="list-group-item border-0 border-bottom rounded-0 {{ $notification->is_read ? 'bg-white' : 'bg-light' }}">
                        <div class="d-flex w-100 justify-content-between align-items-center">
                            <div class="text-secondary">
                                <span>{{ $notification->message }}</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <small class="text-secondary me-3">{{ $notification->created_at->diffForHumans() }}</small>
                                @if(!$notification->is_read)
                                    <button class="btn btn-sm btn-outline-secondary rounded-0 mark-read" data-id="{{ $notification->id }}">
                                        Прочитано
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.mark-read').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.dataset.id;
        fetch(`/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(() => {
            location.reload();
        });
    });
});
</script>
@endpush
@endsection