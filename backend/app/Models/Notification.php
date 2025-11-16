<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    // Tên bảng trong DB
    protected $table = 'notifications';

    // Các trường có thể gán giá trị hàng loạt
    protected $fillable = [
        'user_id',
        'noi_dung',
        'da_doc',
        'ly_do'
    ];

    // Trường kiểu boolean
    protected $casts = [
        'da_doc' => 'boolean',
    ];

    /**
     * Người nhận thông báo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public $timestamps = false;
}
