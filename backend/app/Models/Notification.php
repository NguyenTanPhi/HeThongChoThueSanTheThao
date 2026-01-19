<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory; //cho phép sử dụng factories để tạo dữ liệu mẫu

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'noi_dung',
        'da_doc',
        'ly_do'
    ];

    protected $casts = [
        'da_doc' => 'boolean',
    ];

     // Người nhận thông báo
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public $timestamps = false;
}
