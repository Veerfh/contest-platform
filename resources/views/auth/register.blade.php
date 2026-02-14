@extends('layouts.app')

@section('title', 'Регистрация')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h4 class="mb-0 fw-normal">Регистрация</h4>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label text-secondary">Имя</label>
                        <input type="text" 
                               class="form-control rounded-0 @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required 
                               autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label text-secondary">Email</label>
                        <input type="email" 
                               class="form-control rounded-0 @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label text-secondary">Пароль</label>
                        <input type="password" 
                               class="form-control rounded-0 @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               required>
                        <small class="text-secondary">Минимум 8 символов</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label text-secondary">Подтверждение пароля</label>
                        <input type="password" 
                               class="form-control rounded-0" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="agree" name="agree" required>
                        <label class="form-check-label text-secondary" for="agree">
                            Я согласен на обработку персональных данных
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-outline-secondary w-100 rounded-0 mb-3">
                        Зарегистрироваться
                    </button>
                    
                    <div class="text-center">
                        <p class="mb-0 text-secondary">
                            Уже есть аккаунт? 
                            <a href="{{ route('login') }}" class="text-dark">
                                Войти
                            </a>
                        </p>
                    </div>
                </form>
            
            </div>
        </div>
    </div>
</div>
@endsection