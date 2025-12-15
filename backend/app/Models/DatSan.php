<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DatSan extends Model
{
    protected $table = 'dat_san';
    protected $appends = ['da_hoan_thanh'];
    public $timestamps = false;

    protected $fillable = [
        'san_id', 'user_id', 'ngay_dat', 'gio_bat_dau', 'gio_ket_thuc', 'trang_thai', 'tong_gia', 'ly_do_tu_choi'
    ];

    public function san()
    {
        return $this->belongsTo(San::class, 'san_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function thanhToan()
    {
        return $this->hasOne(ThanhToan::class, 'dat_san_id');
    }
    public function getDaHoanThanhAttribute()
{
    $end = Carbon::parse($this->ngay_dat . ' ' . $this->gio_ket_thuc);
    return now()->greaterThanOrEqualTo($end);
}
}