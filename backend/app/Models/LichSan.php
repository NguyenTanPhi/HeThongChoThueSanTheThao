<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LichSan extends Model
{
    protected $table = 'lich_san';
    public $timestamps = false;

    protected $fillable = [
        'san_id', 'nguoi_dat_id', 'ngay', 'gio_bat_dau', 'gio_ket_thuc', 'trang_thai', 'gia'
    ];
}