<?php

namespace App\Modules\User\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use  Notifiable,  HasRoles, HasApiTokens;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'model_type',
        'email_verified_at',
        'is_enabled',
        'model_id',
    ];

    protected $hidden = [
        'model_type',
        'model_id',
        'password',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
