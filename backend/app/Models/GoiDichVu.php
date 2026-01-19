<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoiDichVu extends Model
{
    use HasFactory; //cho phép sử dụng factories để tạo dữ liệu mẫu

    protected $table = 'goidichvu';

    protected $fillable = [
        'ten_goi',
        'mo_ta',
        'gia',
        'thoi_han',
        'trang_thai'
    ];

    public $timestamps = false;
}
