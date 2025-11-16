<?php

namespace App\Http\Controllers;

use App\Models\DatSan;
use App\Models\LichSan;
use App\Models\San;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class DatSanController extends Controller
{
    public function lichTrong($san_id, $ngay)
    {
        $lich = LichSan::where('san_id', $san_id)
                       ->where('ngay', $ngay)
                       ->where('trang_thai', 'trong')
                       ->get();

        return response()->json($lich);
    }

    public function store(Request $request)
    {
        $request->validate([
            'san_id' => 'required|exists:san,id',
            'ngay_dat' => 'required|date',
            'gio_bat_dau' => 'required',
            'gio_ket_thuc' => 'required|after:gio_bat_dau'
        ]);

        // Kiểm tra trùng lịch
        $trung = DatSan::where('san_id', $request->san_id)
                       ->where('ngay_dat', $request->ngay_dat)
                       ->where('trang_thai', '!=', 'da_huy')
                       ->where(function($q) use ($request) {
                           $q->whereBetween('gio_bat_dau', [$request->gio_bat_dau, $request->gio_ket_thuc])
                             ->orWhereBetween('gio_ket_thuc', [$request->gio_bat_dau, $request->gio_ket_thuc])
                             ->orWhereRaw('? BETWEEN gio_bat_dau AND gio_ket_thuc', [$request->gio_bat_dau]);
                       })->exists();

        if ($trung) {
            return response()->json(['message' => 'Khung giờ đã được đặt'], 400);
        }

        $datSan = DatSan::create([
            'san_id' => $request->san_id,
            'user_id' => $request->user()->id,
            'ngay_dat' => $request->ngay_dat,
            'gio_bat_dau' => $request->gio_bat_dau,
            'gio_ket_thuc' => $request->gio_ket_thuc,
            'trang_thai' => 'cho_duyet',
            'tong_gia' => $request->tong_gia
        ]);

        return response()->json(['message' => 'Đặt sân thành công', 'dat_san' => $datSan], 201);
    }

    public function myBooking(Request $request)
    {
        $bookings = DatSan::with('san')->where('user_id', $request->user()->id)->get();
        return response()->json($bookings);
    }
    public function danhSachChoDuyet(Request $request)
{
    // Lấy sân của chủ
    $sanChu = San::where('owner_id', $request->user()->id)->pluck('id');

    $yeuCau = DatSan::with('user', 'san')
                     ->whereIn('san_id', $sanChu)
                     ->where('trang_thai', 'cho_duyet')
                     ->get();

    return response()->json($yeuCau);
}

public function chiTiet(Request $request, $id)
{
    $datSan = DatSan::with('user', 'san')->findOrFail($id);
    return response()->json($datSan);
}
public function duyetDatSan(Request $request)
{
    $request->validate([
        'dat_san_id' => 'required|integer',
        'trang_thai' => 'required|in:da_duyet,tu_choi'
    ]);

    $datSan = DatSan::with('user', 'san')->findOrFail($request->dat_san_id);

    if ($request->trang_thai === 'da_duyet') {
        $datSan->trang_thai = 'da_duyet';
        $datSan->save();

        // Tạo thông báo cho khách hàng
        Notification::create([
            'user_id' => $datSan->user->id,
            'noi_dung' => "Yêu cầu đặt sân '{$datSan->san->ten_san}' của bạn đã được duyệt. Vui lòng thanh toán.",
            'da_doc' => false
        ]);
    } else {
       try {
    $datSan->trang_thai = 'da_huy';
    $datSan->ly_do_tu_choi = $request->ly_do ?? 'Không có lý do';
    $datSan->save();

    if ($datSan->user && $datSan->san) {
        Notification::create([
            'user_id' => $datSan->user->id,
            'noi_dung' => "Yêu cầu đặt sân '{$datSan->san->ten_san}' của bạn đã bị từ chối.",
            'ly_do' => $datSan->ly_do_tu_choi,
            'da_doc' => false
        ]);
    } else {
        Log::warning('DatSan missing user or san: ID '.$datSan->id);
    }
} catch (\Exception $e) {
    Log::error('Lỗi khi từ chối DatSan ID '.$datSan->id.': '.$e->getMessage());
    return response()->json([
        'success' => false,
        'message' => 'Xảy ra lỗi server khi từ chối yêu cầu.'
    ], 500);
}

        
    }

    return response()->json([
        'success' => true,
        'message' => 'Cập nhật trạng thái thành công',
        'dat_san' => $datSan
    ]);
}
public function getNotifications(Request $request)
{
    $user = $request->user();

    $notifications = Notification::where('user_id', $user->id)
        ->orderByDesc('created_at')
        ->get(['id', 'noi_dung', 'ly_do', 'da_doc', 'created_at']);

    $unreadCount = $notifications->where('da_doc', 0)->count();

    return response()->json([
        'data' => $notifications,
        'unread_count' => $unreadCount
    ]);
}
public function markNotificationRead(Request $request)
{
    $request->validate([
        'notification_id' => 'required|integer'
    ]);

    $user = $request->user();
    $updated = DB::table('notifications')
        ->where('id', $request->notification_id)
        ->where('user_id', $user->id)
        ->where('da_doc', 0)
        ->update(['da_doc' => 1]);

    return response()->json(['success' => (bool)$updated]);
}

}