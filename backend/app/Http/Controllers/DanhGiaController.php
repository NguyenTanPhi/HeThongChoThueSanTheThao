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
use Carbon\Carbon;
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
            ->exists(); //chỉ được đánh giá 1 lần

        return response()->json(['da_danh_gia' => $exists]); 
    }

    //Lưu đánh giá và gửi tb cho owner
    public function store(Request $request)
{
    $user = $request->user();
    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Chưa đăng nhập'
        ], 401);
    }

    $request->validate([
        'san_id'        => 'required|exists:san,id',
        'diem_danh_gia' => 'required|integer|min:1|max:5',
        'noi_dung'      => 'required|string|min:10|max:1000',
    ]);

    $now = Carbon::now();

    // 1️⃣ Tìm booking đã KẾT THÚC
    $booking = DatSan::where('user_id', $user->id)
        ->where('san_id', $request->san_id)
        ->whereRaw(
            "STR_TO_DATE(CONCAT(ngay_dat, ' ', gio_ket_thuc), '%Y-%m-%d %H:%i:%s') < ?",
            [$now]
        )
        ->latest('ngay_dat')
        ->first();

    if (!$booking) {
        return response()->json([
            'success' => false,
            'message' => 'Chỉ được đánh giá sau khi trận đấu kết thúc'
        ], 403);
    }

    // 2️⃣ Không cho đánh giá trùng
    $daTonTai = DanhGia::where('nguoi_dung_id', $user->id)
        ->where('san_id', $request->san_id)
        ->exists();

    if ($daTonTai) {
        return response()->json([
            'success' => false,
            'message' => 'Bạn đã đánh giá sân này rồi!'
        ], 409);
    }

    // 3️⃣ Tạo đánh giá
    $danhGia = DanhGia::create([
        'nguoi_dung_id' => $user->id,
        'san_id'        => $request->san_id,
        'diem_danh_gia' => $request->diem_danh_gia,
        'noi_dung'      => $request->noi_dung,
        'ngay_danh_gia'  => now(),
    ]);

    // 4️⃣ Gửi thông báo cho chủ sân
    $san = San::find($request->san_id);
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
        $danhGia = DB::table('danh_gia') //tối ưu hóa tốc độ truy vấn
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
            'trung_binh' => round($trungBinh, 1), //làm tròn 1 chữ số 
            'tong_so'    => $danhGia->count()
        ]);
    }
}