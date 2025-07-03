@extends('layouts.app')


@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
  <div class="card p-4" style="width: 100%; max-width: 400px; background-color: var(--accent-color); border: 1px solid var(--primary-color);">
    <h2 class="text-center text-primary mb-4">Login</h2>
    <form method="POST" action="{{ route('login') }}">
      {{ csrf_field() }}

      <!-- Login Field -->
      <div class="mb-3">
        <label for="login" class="form-label text-secondary">E-mail or Username</label>
        <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus class="form-control">
        @if ($errors->has('login'))
          <span class="text-danger small">
            {{ $errors->first('login') }}
          </span>
        @endif
      </div>

      <!-- Password Field -->
      <div class="mb-3">
        <label for="password" class="form-label text-secondary">Password</label>
        <input id="password" type="password" name="password" required class="form-control">
        @if ($errors->has('password'))
          <span class="text-danger small">
            {{ $errors->first('password') }}
          </span>
        @endif
      </div>

      <!-- Remember Me -->
      <div class="form-check mb-3">
        <input type="checkbox" name="remember" id="remember" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
        <label class="form-check-label text-secondary" for="remember">Remember Me</label>
      </div>

      <!-- Submit Button -->
      <div class="d-grid mb-3">
        <button type="submit" class="btn btn-primary">Login</button>
      </div>
      <div class="text-center mt-3">
      <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot your password?</a>
    </div>
      <!-- Register Link -->
      <div class="text-center">
        <a href="{{ route('register') }}" class="text-primary">New user? Register here.</a>
      </div>
    </form>
  </div>
  
</div>


<!-- Modal for Forgot Password -->
<div class="text-center mt-3">
  <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="forgotPasswordLabel">Forgot Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="POST" action="{{ route('forgot-password') }}">
            {{ csrf_field() }}
            <div class="mb-3">
              <label for="email" class="form-label">Enter your email</label>
              <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
