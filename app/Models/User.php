<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

use Illuminate\Database\Eloquent\ScopeInterface;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable {
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'first_name',
        'last_name',
        'image',
        'qr_code',
        'amount',
        'phone_number',
        'email',
        'phone_otp',
        'email_otp',
        'is_phone_number_verified',
        'is_email_verified',
        'password',
    ];

    protected $hidden = [
        'password', 'deleted_at', 'created_at', 'updated_at',
    ];

    protected $casts = [
        'id' => 'string',
        'amount' => 'string',
        'phone_otp' => 'string',
        'email_otp' => 'string',
        'is_phone_number_verified' => 'string',
        'is_email_verified' => 'string',
    ];

    protected static function boot() {
        parent::boot();

        static::addGlobalScope('is_phone_number_verified', function (Builder $builder) {
            $builder->where('is_phone_number_verified', 1);
        });
    }

    public function getImageAttribute($input) {
        return url('/uploads/user/' . $input);
    }
}
