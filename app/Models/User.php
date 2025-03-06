<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'users';
    protected $fillable = [
        'username',
        'fullname',
        'email',
        'password',
        'avatar',
        'phone',
        'address',
        'birth_date',
        'status',
        'role',
        'del_flg',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function isSuperAdmin()
    {
        return $this->role === "s.admin";
    }
    
    public function isAdmin()
    {
        return $this->role === "admin";
    }
    public function isClient()
    {
        return $this->role === 'client';
    }
}
