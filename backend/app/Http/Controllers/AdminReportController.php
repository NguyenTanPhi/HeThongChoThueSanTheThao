<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function baoCaoDatSan(Request $request)
{
    $query = DB::table('dat_san')
        ->join('san', 'dat_san.san_id', '=', 'san.id')
        ->join('nguoi_dung', 'dat_san.user_id', '=', 'nguoi_dung.id')
        ->leftJoin('thanh_toan', 'dat_san.id', '=', 'thanh_toan.dat_san_id')
        ->where('dat_san.trang_thai', 'da_thanh_toan')
        ->select(
            'dat_san.id as dat_san_id',
            'san.ten_san',
            'nguoi_dung.name as nguoi_dat',
            'dat_san.ngay_dat',
            'dat_san.gio_bat_dau',
            'dat_san.gio_ket_thuc',
            'dat_san.tong_gia as so_tien'
        );

    // BỘ LỌC THỜI GIAN
    if ($request->filled('from') && $request->filled('to')) {
        $query->whereBetween('dat_san.ngay_dat', [$request->from, $request->to]);
    } elseif ($request->filled('month') && $request->filled('year')) {
        $query->whereMonth('dat_san.ngay_dat', $request->month)
              ->whereYear('dat_san.ngay_dat', $request->year);
    } elseif ($request->filled('year')) {
        $query->whereYear('dat_san.ngay_dat', $request->year);
    }

    return response()->json(
        $query->orderBy('dat_san.id', 'DESC')->get()
    );
}


    public function baoCaoGoiDichVu(Request $request)
{
    $query = DB::table('goidamua')
        ->join('nguoi_dung', 'goidamua.nguoi_dung_id', '=', 'nguoi_dung.id')
        ->leftJoin('goidichvu', 'goidamua.goi_id', '=', 'goidichvu.id')
        ->select(
            'goidamua.id',
            DB::raw("COALESCE(goidichvu.ten_goi, CONCAT('Gói đã xóa #', goidamua.goi_id)) as ten_goi"),
            'nguoi_dung.name as nguoi_dung',
            'goidamua.ngay_mua',
            'goidamua.ngay_het',
            'goidamua.gia'
        );

    // BỘ LỌC THỜI GIAN
    if ($request->filled('from') && $request->filled('to')) {
        $query->whereBetween('goidamua.ngay_mua', [$request->from, $request->to]);
    } elseif ($request->filled('month') && $request->filled('year')) {
        $query->whereMonth('goidamua.ngay_mua', $request->month)
              ->whereYear('goidamua.ngay_mua', $request->year);
    } elseif ($request->filled('year')) {
        $query->whereYear('goidamua.ngay_mua', $request->year);
    }

    return response()->json(
        $query->orderBy('goidamua.id', 'DESC')->get()
    );
}



    //Thống kê
    public function thongKe(Request $request)
    {
        $ownerId = $request->user()->id;

        //Lấy danh sách sân của Owner
        $sanIds = DB::table('san')
            ->where('owner_id', $ownerId)
            ->pluck('id');

        if ($sanIds->isEmpty()) {
            return response()->json([
                "doanh_thu" => 0,
                "so_don" => 0,
                "lich" => []
            ]);
        }

        // Query lọc dữ liệu
        $query = DB::table('dat_san')
            ->whereIn('san_id', $sanIds)
            ->where('trang_thai', 'da_thanh_toan');

        // Lọc theo ngày
        if ($request->ngay) {
            $query->whereDate('ngay_dat', $request->ngay);
        }

        // Lọc theo tháng
        if ($request->thang) {
            $query->whereMonth('ngay_dat', substr($request->thang, 5, 2));
            $query->whereYear('ngay_dat', substr($request->thang, 0, 4));
        }

        // Lọc theo năm
        if ($request->nam) {
            $query->whereYear('ngay_dat', $request->nam);
        }

        // Lọc theo khoảng ngày
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('ngay_dat', [$request->from, $request->to]);
        }

        // Lấy danh sách đơn
        $lich = $query
            ->join('san', 'dat_san.san_id', '=', 'san.id')
            ->select(
                'dat_san.*',
                'san.ten_san'
            )
            ->orderBy('dat_san.created_at', 'DESC')
            ->get();

        // Tính doanh thu
        $tongDoanhThu = $lich->sum('tong_gia');

        return response()->json([
            "doanh_thu" => $tongDoanhThu,
            "so_don" => $lich->count(),
            "lich" => $lich
        ]);
    }
}