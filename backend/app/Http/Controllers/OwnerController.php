<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use Illuminate\Support\Facades\Cache;

class OwnerController extends Controller
{
    /**
     * Xem gói hiện tại
     */
    public function goiHienTai()
{
    try {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Lấy gói mới nhất của user
        $goi = DB::table('goidamua')
            ->join('goidichvu', 'goidamua.goi_id', '=', 'goidichvu.id')
            ->where('goidamua.nguoi_dung_id', $user->id)
            ->orderByDesc('goidamua.ngay_mua')
            ->select('goidichvu.ten_goi', 'goidamua.ngay_mua', 'goidamua.ngay_het', 'goidamua.trang_thai')
            ->first();

        if (!$goi) {
            return response()->json([
                'ten_goi' => null,
                'ngay_het_han' => null,
                'ngay_con_lai' => 0,
                'trang_thai' => 'chua_mua'
            ]);
        }

        // Chỉ dùng ngày để so sánh, bỏ giờ phút
        $ngayHet = strtotime($goi->ngay_het);
        $homNay  = strtotime(date('Y-m-d'));

        $ngayConLai = ceil(($ngayHet - $homNay) / 86400);
        $ngayConLai = max(0, $ngayConLai);

        // Cập nhật trạng thái nếu hết hạn
        if ($ngayHet < $homNay && $goi->trang_thai === 'con_han') {
            DB::table('goidamua')
                ->where('nguoi_dung_id', $user->id)
                ->where('trang_thai', 'con_han')
                ->update(['trang_thai' => 'het_han']);
            $goi->trang_thai = 'het_han';
        }

        return response()->json([
            'ten_goi' => $goi->ten_goi,
            'ngay_het_han' => $goi->ngay_het,
            'ngay_con_lai' => $ngayConLai,
            'trang_thai' => $goi->trang_thai
        ]);

    } catch (\Exception $e) {
        Log::error('Lỗi goiHienTai: ' . $e->getMessage());
        return response()->json(['message' => 'Lỗi server'], 500);
    }
}


    /**
     * Lấy thông báo
     */
    public function getNotifications()
    {
        try {
            $user = Auth::guard('sanctum')->user();
            if (!$user) {
                Log::warning('getNotifications: Không tìm thấy user (sanctum)');
                return response()->json([
                    'notifications' => [],
                    'unread_count' => 0
                ]);
            }

            $notifications = Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $unreadCount = $notifications->where('da_doc', 0)->count();

            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi getNotifications: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Đánh dấu đã đọc thông báo
     */
    public function markNotificationRead($id)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            if (!$user) {
                return response()->json(['message' => 'Chưa đăng nhập'], 401);
            }

            $notif = Notification::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$notif) {
                return response()->json(['message' => 'Không tìm thấy thông báo'], 404);
            }

            $notif->da_doc = 1;
            $notif->save();

            return response()->json(['message' => 'Đã đánh dấu đã đọc']);
        } catch (\Exception $e) {
            Log::error('Lỗi markNotificationRead: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    /**
     * Tạo URL thanh toán VNPay cho gói dịch vụ
     */
    public function taoThanhToan(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $goiId = $request->input('goi_dich_vu_id');
            if (!$goiId) return response()->json(['message' => 'Thiếu gói dịch vụ ID'], 400);

            $goi = DB::table('goidichvu')->where('id', $goiId)->first();
            if (!$goi) return response()->json(['message' => 'Không tìm thấy gói dịch vụ'], 404);

            $amount = $goi->gia ?? 0;
            if ($amount < 1000) return response()->json(['message' => 'Giá gói dịch vụ không hợp lệ'], 400);

            $orderCode = 'SP' . random_int(100000, 999999);

            $vnp_TmnCode    = config('vnpay.vnp_TmnCode');
            $vnp_HashSecret = config('vnpay.vnp_HashSecret');
            $vnp_Url        = config('vnpay.vnp_Url');
            $vnp_ReturnUrl  = config('vnpay.vnp_Returnurl', config('vnpay.vnp_ReturnUrl'));

            if (!$vnp_TmnCode || !$vnp_HashSecret || !$vnp_Url || !$vnp_ReturnUrl) {
                return response()->json(['status' => 500, 'message' => 'Cấu hình VNPAY không hợp lệ!'], 500);
            }

            $inputData = [
                'vnp_Version'   => '2.1.0',
                'vnp_TmnCode'   => $vnp_TmnCode,
                'vnp_Amount'    => $amount * 100,
                'vnp_Command'   => 'pay',
                'vnp_CreateDate'=> now()->format('YmdHis'),
                'vnp_CurrCode'  => 'VND',
                'vnp_IpAddr'    => $request->ip(),
                'vnp_Locale'    => 'vn',
                'vnp_OrderInfo' => 'Thanh toán gói dịch vụ ' . ($goi->ten_goi ?? '') . ' - ' . $orderCode,
                'vnp_OrderType' => 'other',
                'vnp_ReturnUrl' => $vnp_ReturnUrl,
                'vnp_TxnRef'    => $orderCode,
            ];

            ksort($inputData);
            $query = http_build_query($inputData);
            $vnp_SecureHash = hash_hmac('sha512', $query, $vnp_HashSecret);
            $paymentUrl = $vnp_Url . '?' . $query . '&vnp_SecureHash=' . $vnp_SecureHash;

            // Lưu mapping order_code -> user_id, goi_id
            Cache::put('vnp_order_' . $orderCode, [
                'user_id' => $user->id,
                'goi_id'  => $goiId
            ], now()->addMinutes(30));

            return response()->json([
                'success'      => true,
                'message'      => 'Tạo URL thanh toán thành công',
                'payment_url'  => $paymentUrl,
                'order_code'   => $orderCode,
                'user_id'      => $user->id,
                'goi_dich_vu'  => [
                    'id'       => $goi->id,
                    'ten_goi'  => $goi->ten_goi,
                    'gia'      => $goi->gia,
                    'thoi_han' => $goi->thoi_han ?? 30,
                ],
                'goiId' => $goiId
            ]);
        } catch (\Throwable $e) {
            Log::error('VNPay taoThanhToan error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi server'], 500);
        }
    }

    /**
     * VNPay return URL
     */
    public function vnpayReturn(Request $request)
    {
        try {
            $inputData = $request->all();
            $vnp_HashSecret = config('vnpay.vnp_HashSecret');

            $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
            unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);

            ksort($inputData);
            $hashData = '';
            $i = 0;
            foreach ($inputData as $key => $value) {
                $hashData .= ($i ? '&' : '') . urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }

            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
            $frontendUrl = env('URL_FRONTEND', 'http://localhost/HeThongChoThueSanTheThao/frontend/owner/vnpay_return.php');

            if ($secureHash === $vnp_SecureHash && ($inputData['vnp_ResponseCode'] ?? '') === '00') {
                $orderCode = $inputData['vnp_TxnRef'] ?? null;
                $meta = $orderCode ? Cache::pull('vnp_order_' . $orderCode) : null;

                $userId = $meta['user_id'] ?? $inputData['user_id'] ?? null;
                $goiId  = $meta['goi_id']  ?? $inputData['goiId'] ?? null;

                if ($userId && $goiId) {
                    $goi = DB::table('goidichvu')->where('id', $goiId)->first();
                    if ($goi) {
                        $ngayMua = now();
                        $ngayHet = now()->addDays($goi->thoi_han ?? 30);

                        // Insert gói mới trước
                        DB::table('goidamua')->insert([
                            'nguoi_dung_id' => $userId,
                            'goi_id'        => $goiId,
                            'ngay_mua'      => $ngayMua->format('Y-m-d'),
                            'ngay_het'      => $ngayHet->format('Y-m-d'),
                            'trang_thai'    => 'con_han'
                        ]);

                        // Update các gói cũ hết hạn
                        DB::table('goidamua')
                            ->where('nguoi_dung_id', $userId)
                            ->where('trang_thai', 'con_han')
                            ->where('ngay_het', '<', $ngayMua->format('Y-m-d'))
                            ->update(['trang_thai' => 'het_han']);
                    }
                } else {
                    Log::warning('vnpayReturn: Thiếu mapping user_id/goi_id cho order ' . ($orderCode ?? 'N/A'));
                }
            }

            return redirect($frontendUrl . '?' . http_build_query($request->all()));
        } catch (\Throwable $e) {
            Log::error('VNPay vnpayReturn error: ' . $e->getMessage());
            $frontendUrl = env('URL_FRONTEND', 'http://localhost:5173/owner/vnpay-return');
            return redirect($frontendUrl . '?status=fail&message=' . urlencode('Lỗi server'));
        }
    }
}
