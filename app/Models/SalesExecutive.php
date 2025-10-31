<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class SalesExecutive extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $guard = 'sales';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'district',
        'state',
        'pincode',
        'country',
        'bank_name',
        'account_number',
        'ifsc_code',
        'bank_branch',
        'upi_id',
        'total_target',
        'completed_target',
        'income_per_target',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}


