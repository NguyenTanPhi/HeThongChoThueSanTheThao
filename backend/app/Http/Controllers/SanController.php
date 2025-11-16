<?php

namespace App\Http\Controllers;

use App\Models\San;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class SanController extends Controller
{
    public function index(Request $request)
    {
        $query = San::with('owner')
            ->where('trang_thai_duyet', 'da_duyet')
            ->where('trang_thai', 'hoat_dong');

        if ($request->loai_san) {
            $query->where('loai_san', 'like', '%' . $request->loai_san . '%');
        }
        if ($request->dia_chi) {
            $query->where('dia_chi', 'like', '%' . $request->dia_chi . '%');
        }

        $san = $query->paginate(12);

        return response()->json($san);
    }

    public function show($id)
    {
        $san = San::with(['owner', 'danhGia'])->findOrFail($id);
        return response()->json($san);
    }

   public function store(Request $request)
{
    $user = $request->user();

    // KIỂM TRA GÓI CÒN HẠN
    $goi = DB::table('goidamua')
        ->where('nguoi_dung_id', $user->id)
        ->where('trang_thai', 'con_han')
        ->whereDate('ngay_het', '>=', now())
        ->orderByDesc('ngay_mua')
        ->first();

    if (!$goi) {
        // CHECKMARK TRẢ JSON THAY VÌ REDIRECT
        return response()->json([
            'require_package' => true,
            'package_message' => 'Bạn cần mua hoặc gia hạn gói dịch vụ để thêm sân mới.'
        ], 200);
    }

    // XÁC THỰC DỮ LIỆU
    $request->validate([
        'ten_san' => 'required|string|max:255',
        'loai_san' => 'required|string',
        'gia_thue' => 'required|numeric|min:100000',
        'dia_chi' => 'required|string',
        'mo_ta' => 'nullable|string',
        'hinh_anh' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
    ]);

    $data = $request->all();
    $data['owner_id'] = $user->id;
    $data['trang_thai_duyet'] = 'cho_duyet';
    $data['trang_thai'] = 'hoat_dong';

    if ($request->hasFile('hinh_anh')) {    
        $data['hinh_anh'] = $request->file('hinh_anh')->store('san', 'public');
    }

    San::create($data);

    // CHECKMARK TRẢ JSON THÀNH CÔNG THAY VÌ REDIRECT
    return response()->json([
        'success' => true,
        'message' => 'Gửi yêu cầu mở sân thành công! Vui lòng chờ quản trị viên duyệt trong vòng 24h.'
    ], 200);
}

    public function mySan()
{
    try {
        // DÙNG SANCTUM GUARD
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $sanList = \App\Models\San::where('owner_id', $user->id)
            ->select('id', 'ten_san', 'loai_san', 'gia_thue', 'dia_chi', 'trang_thai_duyet')
            ->get()
            ->toArray(); // ĐẢM BẢO LÀ MẢNG

        return response()->json($sanList);
    } catch (\Exception $e) {
        Log::error('Lỗi mySan: ' . $e->getMessage());
        return response()->json(['error' => 'Server error'], 500);
    }
}

    public function update(Request $request, $id)
    {
        $san = San::where('owner_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $san->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành công!',
            'san' => $san
        ]);
    }
    public function destroy(Request $request, $id)
{
    $user = $request->user();
    $san = San::where('owner_id', $user->id)->where('id', $id)->first();

    if (!$san) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy sân hoặc bạn không có quyền xóa'
        ], 404);
    }

    // XÓA ẢNH BẰNG UNLINK() — KHÔNG DÙNG STORAGE
    if ($san->hinh_anh) {
        $filePath = public_path('storage/' . $san->hinh_anh);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $san->delete();

    return response()->json([
        'success' => true,
        'message' => 'Đã xóa sân thành công!'
    ]);
}


// 1. CHỦ SÂN: LẤY LỊCH TRỐNG (kiểm tra gói)
public function getLichTrong(Request $request, $id)
{
    $user = $request->user();
    
    // CHECK GÓI DỊCH VỤ
    $goi = DB::table('goidamua')
        ->where('nguoi_dung_id', $user->id)
        ->where('trang_thai', 'con_han')
        ->whereDate('ngay_het', '>=', now())
        ->exists();

    if (!$goi) {
        return response()->json([
            'success' => false,
            'require_package' => true,
            'message' => 'Bạn cần có gói dịch vụ để xem lịch trống!'
        ], 403);
    }

    $san = \App\Models\San::where('id', $id)->where('owner_id', $user->id)->firstOrFail();

    $lich = DB::table('lich_san')
        ->where('san_id', $id)
        ->where('trang_thai', 'trong')
        ->select('id', 'ngay', 'gio_bat_dau', 'gio_ket_thuc','gia')
        ->orderBy('ngay')
        ->orderBy('gio_bat_dau')
        ->get();

    return response()->json($lich);
}

// 2. CHỦ SÂN: THÊM LỊCH TRỐNG
public function themLichTrong(Request $request, $id)
{
    $user = $request->user();
    
    // CHECK GÓI
    $goi = DB::table('goidamua')
        ->where('nguoi_dung_id', $user->id)
        ->where('trang_thai', 'con_han')
        ->whereDate('ngay_het', '>=', now())
        ->exists();

    if (!$goi) {
        return response()->json([
            'success' => false,
            'require_package' => true,
            'message' => 'Bạn cần mua gói để thêm lịch trống!'
        ], 403);
    }

    $request->validate([
        'ngay' => 'required|date|after_or_equal:today',
        'gio_bat_dau' => 'required|date_format:H:i',
        'gio_ket_thuc' => 'required|date_format:H:i|after:gio_bat_dau',
        'gia' => 'nullable|numeric|min:0'
    ]);
    $san = \App\Models\San::findOrFail($id);
    $gia = $request->gia ?: $san->gia_thue;

    // Kiểm tra trùng
    $trung = DB::table('lich_san')
        ->where('san_id', $id)
        ->where('ngay', $request->ngay)
        ->where('trang_thai', 'trong')
        ->where(function ($q) use ($request) {
            $q->where('gio_bat_dau', '<=', $request->gio_ket_thuc)
              ->where('gio_ket_thuc', '>=', $request->gio_bat_dau);
        })
        ->exists();

    if ($trung) {
        return response()->json(['success' => false, 'message' => 'Khung giờ đã tồn tại!'], 400);
    }

    DB::table('lich_san')->insert([
        'san_id' => $id,
        'nguoi_dat_id' => null,
        'ngay' => $request->ngay,
        'gio_bat_dau' => $request->gio_bat_dau,
        'gio_ket_thuc' => $request->gio_ket_thuc,
        'gia' => $gia,
        'trang_thai' => 'trong',
    ]);

    return response()->json(['success' => true, 'message' => 'Thêm lịch trống thành công!']);
}
public function suaLichTrong(Request $request, $id, $lichId)
{
    $user = $request->user();

    // Kiểm tra gói
    $goi = DB::table('goidamua')
        ->where('nguoi_dung_id', $user->id)
        ->where('trang_thai', 'con_han')
        ->whereDate('ngay_het', '>=', now())
        ->exists();

    if ($request->has('_method') && $request->input('_method') === 'PUT') {
        $request->merge(['_method' => 'PUT']);
    }

    $request->validate([
        'ngay' => 'required|date|after_or_equal:today',
        'gio_bat_dau' => 'required|date_format:H:i',
        'gio_ket_thuc' => 'required|date_format:H:i|after:gio_bat_dau',
        'gia' => 'nullable|numeric|min:0'
    ]);

    $san = San::where('id', $id)->where('owner_id', $user->id)->firstOrFail();
    $gia = $request->gia !== null ? $request->gia : $san->gia_thue;

    // Kiểm tra trùng (trừ chính nó)
    $trung = DB::table('lich_san')
        ->where('san_id', $id)
        ->where('ngay', $request->ngay)
        ->where('trang_thai', 'trong')
        ->where('id', '!=', $lichId)
        ->where(function ($q) use ($request) {
            $q->where('gio_bat_dau', '<=', $request->gio_ket_thuc)
              ->where('gio_ket_thuc', '>=', $request->gio_bat_dau);
        })
        ->exists();

    if ($trung) {
        return response()->json(['success' => false, 'message' => 'Khung giờ đã tồn tại!'], 400);
    }

    $updated = DB::table('lich_san')
        ->where('id', $lichId)
        ->where('san_id', $id)
        ->where('trang_thai', 'trong')
        ->update([
            'ngay' => $request->ngay,
            'gio_bat_dau' => $request->gio_bat_dau,
            'gio_ket_thuc' => $request->gio_ket_thuc,
            'gia' => $gia,
        ]);

    return response()->json([
        'success' => $updated > 0,
        'message' => $updated > 0 ? 'Cập nhật thành công!' : 'Không tìm thấy lịch để sửa!'
    ]);
}

// 3. CHỦ SÂN: XÓA LỊCH TRỐNG
public function xoaLichTrong(Request $request, $id, $lichId)
{
    $user = $request->user();
    $san = \App\Models\San::where('id', $id)->where('owner_id', $user->id)->firstOrFail();

    $deleted = DB::table('lich_san')
        ->where('id', $lichId)
        ->where('san_id', $id)
        ->where('trang_thai', 'trong')
        ->delete();

    return response()->json([
        'success' => $deleted > 0,
        'message' => $deleted > 0 ? 'Xóa thành công!' : 'Không thể xóa!'
    ]);
}

// 4. KHÁCH HÀNG: XEM LỊCH TRỐNG
public function getLichTrongKhach($id)
{
    $san = \App\Models\San::findOrFail($id);

    $lich = DB::table('lich_san')
        ->where('san_id', $id)
        ->where('trang_thai', 'trong')
        ->select('id', 'ngay', 'gio_bat_dau', 'gio_ket_thuc','gia')
        ->orderBy('ngay')
        ->orderBy('gio_bat_dau')
        ->get();

    return response()->json($lich);
}
    
}
