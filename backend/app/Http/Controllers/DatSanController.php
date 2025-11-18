<?php

namespace App\Http\Controllers;

use App\Models\DatSan;
use App\Models\LichSan;
use App\Models\San;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

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
            ->where(function ($q) use ($request) {
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
    /**
     * Tạo URL thanh toán VNPay cho gói dịch vụ
     */
    public function taoThanhToanDatSan(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            Log::info('User ID: ' . $user->id);
            $datSanId = $request->input('san_id');
            if (!$datSanId) {
                return response()->json(['message' => 'Thiếu ID đặt sân'], 400);
            }

            $datSan = DB::table('lich_san')->where('san_id', $datSanId)->first();
            if (!$datSan) {
                return response()->json(['message' => 'Không tìm thấy đơn đặt sân'], 404);
            }
            Log::info('DatSan: ' . json_encode($datSan));
            // Số tiền
            $amount = $datSan->gia ?? 0;
            Log::info('Amount: ' . $amount);
            if ($amount < 1000) {
                return response()->json(['message' => 'Giá trị thanh toán không hợp lệ'], 400);
            }

            // Tạo mã đơn hàng VNPay
            $orderCode = 'DS' . random_int(100000, 999999);

            $vnp_TmnCode    = config('vnpay.vnp_TmnCode');
            $vnp_HashSecret = config('vnpay.vnp_HashSecret');
            $vnp_Url        = config('vnpay.vnp_Url');
            $vnp_ReturnUrl  = config('vnpay.vnp_DatSan_Returnurl');

            $inputData = [
                'vnp_Version'   => '2.1.0',
                'vnp_TmnCode'   => $vnp_TmnCode,
                'vnp_Amount'    => $amount * 100,
                'vnp_Command'   => 'pay',
                'vnp_CreateDate' => now()->format('YmdHis'),
                'vnp_CurrCode'  => 'VND',
                'vnp_IpAddr'    => $request->ip(),
                'vnp_Locale'    => 'vn',
                'vnp_OrderInfo' => 'Thanh toán đặt sân #' . $datSanId,
                'vnp_OrderType' => 'billpayment',
                'vnp_ReturnUrl' => $vnp_ReturnUrl,
                'vnp_TxnRef'    => $orderCode,
            ];

            ksort($inputData);
            $query    = http_build_query($inputData);
            $secureHash = hash_hmac('sha512', $query, $vnp_HashSecret);

            $paymentUrl = $vnp_Url . '?' . $query . '&vnp_SecureHash=' . $secureHash;

            // Lưu mapping order_code -> dat_san_id
            Cache::put('vnp_dat_san_' . $orderCode, [
                'user_id'    => $user->id,
                'san_id' => $datSanId,
                'ngay' => $datSan->ngay,
                'gio_bat_dau' => $datSan->gio_bat_dau,
                'gio_ket_thuc' => $datSan->gio_ket_thuc,
            ], now()->addMinutes(30));

            Log::info('cache' . json_encode(Cache::get('vnp_dat_san_' . $orderCode)));

            return response()->json([
                'success'     => true,
                'payment_url' => $paymentUrl,
                'order_code'  => $orderCode,
                'dat_san_id'  => $datSanId
            ]);
        } catch (\Throwable $e) {
            Log::error('VNPay taoThanhToanDatSan: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi server'], 500);
        }
    }

    /**
     * VNPay return URL (xác thực, lưu DB và chuyển hướng về frontend)
     */
    public function vnpayReturnDatSan(Request $request)
    {
        Log::info('VNPay Return Data: ' . json_encode($request->all()));
        try {
            Log::info('VNPay Return Data1: ' . json_encode($request->all()));
            $inputData = $request->all();
            $vnp_HashSecret = config('vnpay.vnp_HashSecret');

            $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
            unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

            ksort($inputData);
            $hashData = '';
            $i = 0;
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
                } else {
                    $hashData .= urlencode($key) . '=' . urlencode($value);
                    $i = 1;
                }
            }

            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

            $frontendUrl = env(
                'URL_FRONTEND_DAT_SAN_RETURN',
                'http://localhost/HeThongChoThueSanTheThao/frontend/customer/vnpay_return.php'
            );

            if (
                $secureHash === $vnp_SecureHash &&
                ($inputData['vnp_ResponseCode'] ?? '') === '00'
            ) {

                $orderCode = $inputData['vnp_TxnRef'] ?? null;
                Log::info('OrderCode for cache: vnp_dat_san_' . $orderCode);
                $meta = $orderCode ? Cache::pull('vnp_dat_san_' . $orderCode) : null;
                Log::info('Meta from cache: ' . json_encode($meta));

                if (!$meta) {
                    Log::warning('Không lấy được thông tin từ cache với key: vnp_dat_san_' . $orderCode);
                }

                $userId = $meta['user_id'] ?? null;
                $sanId = $meta['san_id'] ?? null;
                $ngay = $meta['ngay'] ?? null;
                $gio_bat_dau = $meta['gio_bat_dau'] ?? null;
                $gio_ket_thuc = $meta['gio_ket_thuc'] ?? null;
                if (!$userId) $userId = $inputData['user_id'] ?? null;
                if (!$sanId)  $sanId  = $inputData['san_id'] ?? null;
                Log::info('VNPay Return Meta: ' . json_encode($meta));
                if ($sanId && $userId) {

                    DB::table('dat_san')->insert([
                        'san_id' => $sanId,
                        'user_id' => $userId,
                        'ngay_dat' => $ngay,
                        'gio_bat_dau' => $gio_bat_dau,
                        'gio_ket_thuc' => $gio_ket_thuc,
                        'tong_gia' => $inputData['vnp_Amount'] / 100,
                        'trang_thai' => 'da_thanh_toan',
                    ]);

                    DB::table('thanh_toan')->insert([
                        'dat_san_id' => $sanId,
                        'so_tien'    => $inputData['vnp_Amount'] / 100,
                        'ma_giao_dich' => $inputData['vnp_TransactionNo'] ?? '',
                        'phuong_thuc'  => 'VNPay',
                        'ngay_thanh_toan'    => now(),
                    ]);

                    DB::table('lich_san')
                        ->where('san_id', $sanId)
                        ->where('ngay', $ngay)
                        ->where('gio_bat_dau', $gio_bat_dau)
                        ->where('gio_ket_thuc', $gio_ket_thuc)
                        ->update([
                            'trang_thai' => 'da_dat',
                            'nguoi_dat_id' => $userId
                        ]);
                }
            }

            return redirect($frontendUrl . '?' . http_build_query($request->all()));
        } catch (\Throwable $e) {
            Log::error('VNPay vnpayReturnDatSan: ' . $e->getMessage());
            $frontendUrl = env(
                'URL_FRONTEND_DAT_SAN_RETURN',
                'http://localhost/HeThongChoThueSanTheThao/frontend/customer/vnpay_return.php'
            );
            return redirect($frontendUrl . '?status=fail&message=' . urlencode('Lỗi server'));
        }
    }
}
