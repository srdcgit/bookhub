@extends('layouts.app')
@section('title')
    Sales Dashboard
@endsection
@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold">Welcome {{ (Auth::guard('sales')->user()->name ?? ($user->name ?? 'Sales Executive')) }}</h3>
            <h6 class="text-muted mb-0">All systems are running smoothly!</h6>
        </div>
    </div>
    <div class="row g-4">
        {{-- First Column --}}
        <div class="col-md-6">
            <div class="row g-4">
                <div class="col-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex flex-column align-items-start">
                            <div class="mb-2">
                                <i class="bi bi-building fs-2 text-primary"></i>
                            </div>
                            <p class="mb-1 text-muted">Total Institutions</p>
                            <h3 class="fw-semibold">{{ number_format($sectionsCount ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card shadow-sm border-0 h-100 bg-primary text-white">
                        <div class="card-body d-flex flex-column align-items-start">
                            <div class="mb-2">
                                <i class="bi bi-person-lines-fill fs-2"></i>
                            </div>
                            <p class="mb-1">Total Students</p>
                            <h3 class="fw-semibold">{{ number_format($categoriesCount ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card shadow-sm border-0 h-100 bg-info text-white">
                        <div class="card-body d-flex flex-column align-items-start">
                            <div class="mb-2">
                                <i class="bi bi-box2 fs-2"></i>
                            </div>
                            <p class="mb-1">Total Blocks</p>
                            <h3 class="fw-semibold">{{ number_format($productsCount ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card shadow-sm border-0 h-100 bg-danger text-white">
                        <div class="card-body d-flex flex-column align-items-start">
                            <div class="mb-2">
                                <i class="bi bi-grid-3x3-gap fs-2"></i>
                            </div>
                            <p class="mb-1">Total Classes</p>
                            <h3 class="fw-semibold">{{ number_format($vendorsCount ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Second Column --}}
        <div class="col-md-6">
            <div class="row g-4">
                <div class="col-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex flex-column align-items-start">
                            <div class="mb-2">
                                <i class="bi bi-bag-fill fs-2 text-success"></i>
                            </div>
                            <p class="mb-1 text-muted">Total Orders</p>
                            <h3 class="fw-semibold">{{ number_format($ordersCount ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card shadow-sm border-0 h-100 bg-primary text-white">
                        <div class="card-body d-flex flex-column align-items-start">
                            <div class="mb-2">
                                <i class="bi bi-ticket-perforated-fill fs-2"></i>
                            </div>
                            <p class="mb-1">Total Coupons</p>
                            <h3 class="fw-semibold">{{ number_format($couponsCount ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card shadow-sm border-0 h-100 bg-info text-white">
                        <div class="card-body d-flex flex-column align-items-start">
                            <div class="mb-2">
                                <i class="bi bi-people-fill fs-2"></i>
                            </div>
                            <p class="mb-1">Total Users</p>
                            <h3 class="fw-semibold">{{ number_format($usersCount ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card shadow-sm border-0 h-100 bg-danger text-white">
                        <div class="card-body d-flex flex-column align-items-start">
                            <div class="mb-2">
                                <i class="bi bi-envelope-paper-fill fs-2"></i>
                            </div>
                            <p class="mb-1">Total Subscribers</p>
                            <h3 class="fw-semibold">{{ number_format($subscribersCount ?? 0) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
