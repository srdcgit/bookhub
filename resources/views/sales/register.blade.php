@extends('layouts.guest')

@section('content')
<style>
    body {
        background: linear-gradient(135deg, #1f3c88 0%, #2a5298 100%);
        font-family: 'Poppins', sans-serif;
    }

    .register-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card {
        border: none;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }

    .card-body {
        padding: 2.8rem;
    }

    .brand-logo {
        width: 80px;
        height: 80px;
        object-fit: contain;
        margin-bottom: 1rem;
    }

    .form-label {
        font-weight: 600;
        color: #333;
    }

    .form-control {
        border-radius: 10px;
        padding: 0.8rem 1rem;
        font-size: 0.95rem;
    }

    .input-group-text {
        border-radius: 10px 0 0 10px;
        background-color: #f8f9fa;
    }

    .btn-primary {
        background: #1f3c88;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        padding: 0.75rem;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: #2a5298;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(42, 82, 152, 0.35);
    }

    .text-primary {
        color: #1f3c88 !important;
    }

    .card-footer {
        background: #f8f9fa;
        border-top: none;
        text-align: center;
        padding: 1.25rem;
    }

    .card-footer a {
        color: #1f3c88;
        font-weight: 600;
        text-decoration: none;
        transition: 0.3s;
    }

    .card-footer a:hover {
        color: #2a5298;
        text-decoration: underline;
    }

    .small-text {
        font-size: 0.85rem;
        color: #6c757d;
    }

    @media (max-width: 768px) {
        .card-body {
            padding: 2rem;
        }
    }
</style>

<div class="register-wrapper">
    <div class="col-md-8 col-lg-6 col-xl-5">
        <div class="card">
            <div class="card-body text-center">
                <img src="{{ asset('uploads/logos/' . $logos->first()->logo) }}" style="height:60px; width:220px;" alt="BookHub Logo" class="brand-logo mb-3">
                <h3 class="fw-bold text-primary mb-2">Sales Executive Registration</h3>
                <p class="text-muted mb-4">Create your BookHub Sales account to access your dashboard</p>

                <form method="POST" action="{{ route('sales.register.submit') }}">
                    @csrf

                    {{-- Name --}}
                    <div class="mb-3 text-start">
                        <label for="name" class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="name" id="name" class="form-control" placeholder="Enter your full name" value="{{ old('name') }}" required>
                        </div>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-3 text-start">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email address" value="{{ old('email') }}" required>
                        </div>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Phone --}}
                    <div class="mb-3 text-start">
                        <label for="phone" class="form-label">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="text" name="phone" id="phone" class="form-control" placeholder="Enter your phone number" value="{{ old('phone') }}" required>
                        </div>
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-3 text-start">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Create a strong password" required>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="mb-4 text-start">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirm your password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-person-plus me-2"></i>Register
                    </button>
                </form>

                <p class="small-text mb-0">
                    By registering, you agree to BookHub’s
                </p>
            </div>

            <div class="card-footer">
                <p class="mb-0">Already have an account?
                    <a href="{{ route('sales.login') }}">Login here</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
