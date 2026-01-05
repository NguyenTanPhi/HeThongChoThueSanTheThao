<?php

namespace App\Http\Controllers;

use App\Models\DanhGia;
use App\Models\DatSan;
use App\Models\San;
use App\Models\Notification; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\Input;

class DanhGiaController extends Controller
{
    //Kiểm tra đánh giá hay chưa
    public function checkDaDanhGia(Request $request)
    {
        $nguoi_dung_id = auth()->id();
        $san_id = $request->query('san_id');

        if (!$nguoi_dung_id || !$san_id) {
            return response()->json(['da_danh_gia' => false]);
        }

        $exists = DB::table('danh_gia')
            ->where('nguoi_dung_id', $nguoi_dung_id)
            ->where('san_id', $san_id)
            ->exists();

        return response()->json(['da_danh_gia' => $exists]);
    }

    //Lưu đánh giá và gửi tb cho owner
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập'], 401);
        }

        $request->validate([
            'san_id'        => 'required|exists:san,id',
            'diem_danh_gia' => 'required|integer|min:1|max:5',
            'noi_dung'      => 'required|string|min:10|max:1000',
        ]);

        $san_id = $request->san_id;
        $nguoi_dung_id = $user->id;
        $ngay_dat= Input::$ngay_dat;

          $now = now(); // thời gian hiện tại

$booking = DatSan::where('user_id', $nguoi_dung_id)
    ->where('san_id', $san_id)
    ->whereDate('ngay_dat', '<=', $now->toDateString()) // chỉ các trận đã/đang diễn ra
    ->whereTime('gio_bat_dau', '<=', $now->format('H:i:s')) // bắt đầu trước giờ hiện tại
    ->whereTime('gio_ket_thuc', '>=', $now->format('H:i:s')) // kết thúc sau giờ hiện tại
    ->latest('ngay_dat')
    ->first();
     //  Log::info('booking', ['booking' => $booking->toArray()]);

if ($booking) {
    Log::info('booking', ['booking' => $booking->toArray()]);
} else {
    Log::info('booking not found', [
        'user_id' => $nguoi_dung_id,
        'san_id' => $san_id,
    ]);

    return response()->json([
        'success' => false,
        'message' => 'Không tìm thấy lịch đặt'
    ], 404);
}

// Tính thời gian kết thúc trận đấu
$gioKetThuc = strtotime($booking->ngay_dat . ' ' . $booking->gio_ket_thuc);
Log::info('gio ket thuc',[ 'gio'=>$gioKetThuc]);
$now = time();

Log::info('booking debug', [
    'ngay_dat' => $booking->ngay_dat,
    'gio_ket_thuc' => $booking->gio_ket_thuc,
    'gioKetThuc' => $gioKetThuc,
    'now' => $now,
    'da_hoan_thanh' => $booking->da_hoan_thanh
]);

// Nếu giờ kết thúc đã qua nhưng da_hoan_thanh chưa true → cập nhật
if ($gioKetThuc !== false && $now >= $gioKetThuc && !$booking->da_hoan_thanh) {
    Log::info('Đang cập nhật da_hoan_thanh');
    $booking->da_hoan_thanh = 1;
    $booking->save();
} $booking->save();


        $daHoanThanh = DatSan::where('user_id', $nguoi_dung_id)
    ->where('san_id', $san_id)
    ->where('da_hoan_thanh', true)
    ->exists();

if (!$daHoanThanh) {
    return response()->json([
        'success' => false,
        'message' => 'Chỉ được đánh giá sau khi hoàn thành trận đấu'
    ], 403);
}


        // Kiểm tra đã đánh giá chưa
        $daTonTai = DanhGia::where('nguoi_dung_id', $nguoi_dung_id)
                           ->where('san_id', $san_id)
                           ->exists();

        if ($daTonTai) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn đã đánh giá sân này rồi!'
            ], 400);
        }

        // Tạo đánh giá
        $danhGia = DanhGia::create([
            'nguoi_dung_id' => $nguoi_dung_id,
            'san_id'        => $san_id,
            'diem_danh_gia' => $request->diem_danh_gia,
            'noi_dung'      => $request->noi_dung,
        ]);

        //Gửi thông báo cho owner
        $san = San::find($san_id);
        if ($san && $san->owner_id) {
            Notification::create([
                'user_id'  => $san->owner_id,
                'noi_dung' => "Khách hàng {$user->name} vừa đánh giá sân \"{$san->ten_san}\" – {$request->diem_danh_gia}★",
                'ly_do'    => $request->noi_dung,
                'da_doc'   => 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn bạn đã đánh giá!',
            'data'    => $danhGia
        ], 201);
    }

    //Lấy đánh giá của sân
    public function getBySan($san_id)
    {
        $danhGia = DB::table('danh_gia')
            ->join('nguoi_dung', 'danh_gia.nguoi_dung_id', '=', 'nguoi_dung.id')
            ->where('danh_gia.san_id', $san_id)
            ->select([
                'danh_gia.*',
                'nguoi_dung.name as ten_nguoi_dung',
                'nguoi_dung.avatar'
            ])
            ->orderBy('ngay_danh_gia', 'desc')
            ->get();

        $trungBinh = DB::table('danh_gia')
            ->where('san_id', $san_id)
            ->avg('diem_danh_gia') ?? 0;

        return response()->json([
            'danh_gia'   => $danhGia,
            'trung_binh' => round($trungBinh, 1),
            'tong_so'    => $danhGia->count()
        ]);
    }
}