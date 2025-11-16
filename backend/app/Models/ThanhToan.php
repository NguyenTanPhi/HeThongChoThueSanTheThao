<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThanhToan extends Model
{
    protected $table = 'thanh_toan';
    public $timestamps = false;

    protected $fillable = [
        'dat_san_id', 'ma_giao_dich', 'so_tien', 'phuong_thuc',
        'ngay_thanh_toan', 'trang_thai', 'ghi_chu'
    ];
}