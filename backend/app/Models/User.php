<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'nguoi_dung';
    public $timestamps = false;
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'google_id',
        'address', 'avatar', 'role'
    ];

    protected $hidden = ['password'];

    // BẮT BUỘC: Laravel cần các hàm này
    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getRememberToken()
    {
        return null; // không dùng remember_token
    }

    public function setRememberToken($value)
    {
        // không làm gì
    }

    public function getRememberTokenName()
    {
        return '';
    }

    // Quan hệ
    public function san()
    {
        return $this->hasMany(\App\Models\San::class, 'owner_id');
    }

    public function datSan()
    {
        return $this->hasMany(\App\Models\DatSan::class, 'user_id');
    }
}