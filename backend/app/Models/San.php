<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class San extends Model
{
    protected $table = 'san';
    public $timestamps = false;

    protected $fillable = [
        'owner_id', 'ten_san', 'loai_san', 'gia_thue', 'dia_chi',
        'mo_ta', 'hinh_anh', 'trang_thai', 'trang_thai_duyet', 'ngay_duyet', 'ly_do_tu_choi',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function lichSan()
    {
        return $this->hasMany(LichSan::class, 'san_id');
    }

    public function lichTrong()
{
    return $this->hasMany(LichSan::class, 'san_id')
        ->where('trang_thai', 'trong')
          ->where('ngay', '>=', now()->toDateString())
        ->orderBy('ngay')
        ->orderBy('gio_bat_dau')
        ->limit(5); 
}


    public function datSan()
    {
        return $this->hasMany(DatSan::class, 'san_id');
    }

    public function danhGia()
    {
        return $this->hasMany(DanhGia::class, 'san_id');
    }
    
}