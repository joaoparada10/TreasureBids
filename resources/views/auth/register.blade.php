@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
  <div class="card p-4" style="width: 100%; max-width: 500px; background-color: var(--accent-color); border: 1px solid var(--primary-color);">
    <h2 class="text-center text-primary mb-4">Register</h2>
    <form method="POST" action="{{ route('register') }}">
      {{ csrf_field() }}

      <!-- Username Field -->
      <div class="mb-3">
        <label for="username" class="form-label text-secondary">Username</label>
        <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus class="form-control">
        @if ($errors->has('username'))
          <span class="text-danger small">
            {{ $errors->first('username') }}
          </span>
        @endif
      </div>

      <!-- Email Field -->
      <div class="mb-3">
        <label for="email" class="form-label text-secondary">E-Mail Address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required class="form-control">
        @if ($errors->has('email'))
          <span class="text-danger small">
            {{ $errors->first('email') }}
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

      <!-- Confirm Password Field -->
      <div class="mb-3">
        <label for="password-confirm" class="form-label text-secondary">Confirm Password</label>
        <input id="password-confirm" type="password" name="password_confirmation" required class="form-control">
      </div>

      <!-- Submit Button -->
      <div class="d-grid mb-3">
        <button type="submit" class="btn btn-primary">Register</button>
      </div>

      <!-- Login Link -->
      <div class="text-center">
        <a href="{{ route('login') }}" class="text-primary">Already have an account? Login here.</a>
      </div>
    </form>
  </div>
</div>
@endsection
