<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DanhGia extends Model
{
    protected $table = 'danh_gia';
    public $timestamps = false;

    protected $fillable = ['nguoi_dung_id', 'san_id', 'diem_danh_gia', 'noi_dung','ngay_danh_gia'];
     public function san()
    {
        return $this->belongsTo(San::class, 'san_id');
    }
    
}