<?php

<<<<<<< HEAD

// use Illuminate\Contracts\Auth\MustVerifyEmail;
namespace App\Models;

=======
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
>>>>>>> d0ab644dbf1527382f139f404a86c764df80647c
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
<<<<<<< HEAD
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
        'user_type',
        'status',
        'role',
    ];
=======

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

>>>>>>> d0ab644dbf1527382f139f404a86c764df80647c
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
<<<<<<< HEAD

    public function isSuperAdmin()
    {
        return $this->role === 0;
    }

    public function isAdmin()
    {
        return $this->role === 1;
    }
=======
>>>>>>> d0ab644dbf1527382f139f404a86c764df80647c
}
