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
    protected $guard = 'web';

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'google_id',
        'address', 'avatar', 'role'
    ];

    protected $hidden = ['password'];

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
        return null; 
    }

    public function setRememberToken($value)
    {
        
    }
    public function getRememberTokenName()
    {
        return '';
    }

    //Quan hệ
    public function san()
    {
        return $this->hasMany(\App\Models\San::class, 'owner_id');
    }

    public function datSan()
    {
        return $this->hasMany(\App\Models\DatSan::class, 'user_id');
    }
}