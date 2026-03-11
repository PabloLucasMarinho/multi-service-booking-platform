@extends('components.app-layout')

@section('title', 'Login')

@section('content')
  <section class="auth-container">
    @if (session('status'))
      <span class="success-message">{{ session('status') }}</span>
    @endif

    <form action="{{ route('login') }}" method="POST" class="form-auth">
      @csrf

      <h1>Login</h1>
      <p>Boas vindas de volta!</p>

      <div class="input-wrapper">
        <label for="email">E-mail<span class="required">*</span></label>
        <input type="email" name="email" id="email" autocomplete="email" required/>
        @error('email')
        <small class="error-message">{{ $message }}</small>
        @enderror
      </div>

      <div class="input-wrapper">
        <label for="password">Senha<span class="required">*</span></label>
        <div class="password">
          <input type="password" name="password" id="password" autocomplete="current-password" class="input-password"
                 required/>
          <span class="material-symbols-rounded show">
            visibility
          </span>
        </div>
        @error('password')
        <small class="error-message">{{ $message }}</small>
        @enderror

        <div class="remember-me-wrapper">
          <label for="remember-me">
            <input type="checkbox" name="remember-me" id="remember-me"> <small>Lembrar de mim</small>
          </label>
        </div>
      </div>

      <div class="submit-wrapper">
        <input type="submit" class="btn" value="Entrar">
        <small><a href="{{ route('password.request') }}">Esqueceu sua senha?</a></small>
      </div>
    </form>

  </section>
@endsection
