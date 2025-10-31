@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card shadow-lg border-0 mb-5">
                <div class="card-body p-5">
                    <div class="mb-4 text-center">
                        <h1 class="card-title fw-bold mb-2 text-primary">Sales Executive Dashboard</h1>
                        <p class="card-text text-muted mb-4">Welcome, {{ $user->name }}.</p>
                    </div>
                    <form method="POST" action="{{ route('sales.logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
            <div class="text-center text-muted small">
                &copy; {{ now()->year }} MyBrand. All rights reserved.
            </div>
        </div>
    </div>
</div>
@endsection
