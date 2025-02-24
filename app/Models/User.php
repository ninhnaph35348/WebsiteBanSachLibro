<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username', 'fullname', 'email', 'password', 'avatar',
        'phone', 'address', 'birth_date', 'user_type', 'role', 'status'
    ];

    protected $hidden = ['password'];

    protected $casts = ['password' => 'hashed'];

    public function isSuperAdmin()
    {
        return $this->role === 0;
    }

    public function isAdmin()
    {
        return $this->role === 1;
    }
}
