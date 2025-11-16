<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\San;
use App\Models\LichSan;

class CustomerSanController extends Controller
{
    /**
     * 📋 Danh sách sân bóng
     */
    public function index()
    {
        $sanList = San::where('trang_thai', 'hoat_dong')
                      ->where('trang_thai_duyet', 'da_duyet')
                      ->get();

        return response()->json(['data' => $sanList]);
    }

    /**
     * 📋 Chi tiết sân
     */
    public function show($id)
    {
        $san = San::with('owner', 'danhGia')->find($id);

        if (!$san) {
            return response()->json(['message' => 'Không tìm thấy sân'], 404);
        }

        return response()->json(['data' => $san]);
    }

    /**
     * 📋 Lấy lịch trống của sân cho khách hàng
     * Chỉ hiển thị các khung giờ mà chủ sân đã khai báo
     */
    public function lichTrong($id)
    {
        $san = San::find($id);
        if (!$san) {
            return response()->json(['message' => 'Không tìm thấy sân'], 404);
        }

        // Lấy các khung giờ trống do chủ sân đăng
        $lichTrong = LichSan::where('san_id', $id)
                             ->where('ngay', '>=', now()->format('Y-m-d'))
                             ->where('trang_thai', 'trong') // chỉ lấy slot trống
                             ->orderBy('ngay', 'asc')
                             ->orderBy('gio_bat_dau', 'asc')
                             ->get(['ngay', 'gio_bat_dau', 'gio_ket_thuc', 'gia']);

        return response()->json(['data' => $lichTrong]);
    }
}
