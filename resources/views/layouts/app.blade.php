<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Конкурсная платформа')</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #1a252f 100%);
            color: white;
        }
        .sidebar a {
            color: rgba(255,255,255,.8);
            text-decoration: none;
            padding: 10px 20px;
            display: block;
            transition: all 0.3s;
        }
        .sidebar a:hover {
            background: rgba(255,255,255,.1);
            color: white;
        }
        .sidebar a.active {
            background: rgba(255,255,255,.1);
            color: white;
        }
        .sidebar i {
            width: 25px;
            margin-right: 10px;
        }
        .main-content {
            padding: 30px;
        }
        .notification-badge {
            font-size: 0.6rem;
            padding: 0.2rem 0.4rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 p-0 sidebar">
                <div class="p-4">
                    <h5 class="text-white mb-4">
                        Конкурсы
                    </h5>
                    
                    <nav>
                        @auth
                            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                Дашборд
                            </a>
                            
                            <a href="{{ route('submissions.index') }}" class="{{ request()->routeIs('submissions.*') ? 'active' : '' }}">
                                Работы
                            </a>
                            
                            @if(auth()->user()->isJury() || auth()->user()->isAdmin())
                                <a href="{{ route('jury.submissions') }}" class="{{ request()->routeIs('jury.*') ? 'active' : '' }}">
                                    Жюри
                                </a>
                            @endif
                            
                            @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.contests') }}" class="{{ request()->routeIs('admin.contests') ? 'active' : '' }}">
                                    Управление
                                </a>
                                <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                                    Пользователи
                                </a>
                            @endif
                            
                            <hr class="bg-white">
                            
                            <a href="{{ route('notifications.index') }}" class="d-flex justify-content-between align-items-center">
                                <span>
                                    Уведомления
                                </span>
                                <span class="badge bg-danger rounded-pill notification-badge" style="display: none;">0</span>
                            </a>
                            
                            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Выйти
                            </a>
                            
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        @endauth
                    </nav>
                </div>
            </div>
            
            <div class="col-md-10 main-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    @stack('scripts')
    
    @auth
    @push('scripts')
    <script>
    function updateNotificationCount() {
        fetch('{{ route("notifications.unread-count") }}')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.notification-badge');
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline';
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(error => console.error('Error fetching notification count:', error));
    }

    setInterval(updateNotificationCount, 30000);
    
    document.addEventListener('DOMContentLoaded', function() {
        updateNotificationCount();
    });
    </script>
    @endpush
    @endauth
</body>
</html>