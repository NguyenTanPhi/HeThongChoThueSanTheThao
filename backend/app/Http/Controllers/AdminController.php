<?php

namespace App\Http\Controllers;

use App\Models\San;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Notification;

class AdminController extends Controller
{
    public function sanChoDuyet()
    {
        $san = San::where('trang_thai_duyet', 'cho_duyet')->with('owner')->get();
        return response()->json($san);
    }

    public function duyetSan(Request $request, $id)
    {
        $san = San::findOrFail($id);

        $request->validate([
            'trang_thai_duyet' => 'required|in:da_duyet,tu_choi',
            'ly_do' => 'nullable|string|max:500|required_if:trang_thai_duyet,tu_choi'
        ]);

        $oldStatus = $san->trang_thai_duyet;
        $san->trang_thai_duyet = $request->trang_thai_duyet;
        $san->ngay_duyet = now();

        if ($request->trang_thai_duyet === 'tu_choi') {
            $san->ly_do_tu_choi = $request->ly_do;
        } else {
            $san->ly_do_tu_choi = null;
        }

        $san->save();

        // === GỬI THÔNG BÁO CHO CHỦ SÂN ===
        $owner = $san->owner; // Đã load từ with('owner')

        if ($owner) {
            $noi_dung = '';
            $ly_do_thong_bao = null;

            if ($request->trang_thai_duyet === 'da_duyet') {
                $noi_dung = "Chúc mừng! Sân '{$san->ten_san}' của bạn đã được duyệt và chính thức hoạt động trên hệ thống.";
            } elseif ($request->trang_thai_duyet === 'tu_choi') {
                $noi_dung = "Yêu cầu đăng ký sân '{$san->ten_san}' của bạn đã bị từ chối.";
                $ly_do_thong_bao = $request->ly_do;
            }

            Notification::create([
                'user_id' => $owner->id,
                'noi_dung' => $noi_dung,
                'ly_do' => $ly_do_thong_bao,
                'da_doc' => 0,
                'created_at' => now(),
            ]);
        }

        $action = $request->trang_thai_duyet === 'da_duyet' ? 'duyệt' : 'từ chối';
        return response()->json([
            'message' => "Đã $action sân thành công!",
            'san' => $san->fresh(['owner'])
        ]);
    }

    /**
     * Danh sách người dùng
     */
    public function users()
    {
        $users = User::select('id', 'name', 'email', 'role', 'created_at')->get();
        return response()->json($users);
    }
}