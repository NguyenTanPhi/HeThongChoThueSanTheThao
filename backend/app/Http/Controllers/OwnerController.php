<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Cache; // <-- thêm Cache

class OwnerController extends Controller
{
    /**
     * Mua gói dịch vụ
     */
    public function muaGoi(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $goiId = $request->input('goi_dich_vu_id');
            if (!$goiId) {
                return response()->json(['message' => 'Thiếu gói dịch vụ ID'], 400);
            }

            $orderId = 'MOMO_' . time() . '_' . $user->id;
            $payUrl = "https://img.vietqr.io/image/mbbank-0909101911-compact2.jpg?amount=100000&addInfo=Thanh%20toan%20goi%20{$orderId}&accountName=LUAN%20VAN%20TOT%20NGHIEP";

            return response()->json([
                'success' => true,
                'payUrl' => $payUrl,
                'orderId' => $orderId,
                'message' => 'Tạo QR thành công!'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi mua gói: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Kiểm tra thanh toán
     */
    public function checkThanhToan(Request $request, $orderId)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            sleep(3); // Giả lập

            $goiId = $request->input('goi_dich_vu_id');
            if (!$goiId) {
                return response()->json(['message' => 'Thiếu gói dịch vụ ID'], 400);
            }

            $goi = DB::table('goidichvu')->where('id', $goiId)->first();
            if (!$goi) {
                return response()->json(['message' => 'Không tìm thấy gói dịch vụ'], 404);
            }

            DB::table('goidamua')
                ->where('nguoi_dung_id', $user->id)
                ->where('trang_thai', 'con_han')
                ->update(['trang_thai' => 'het_han']);

            $ngayMua = now();
            $ngayHet = now()->addDays($goi->thoi_han ?? 30);

            DB::table('goidamua')->insert([
                'nguoi_dung_id' => $user->id,
                'goi_id' => $goiId,
                'ngay_mua' => $ngayMua->format('Y-m-d'),
                'ngay_het' => $ngayHet->format('Y-m-d'),
                'trang_thai' => 'con_han'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thanh toán thành công!',
                'data' => [
                    'ten_goi' => $goi->ten_goi,
                    'ngay_mua' => $ngayMua->format('Y-m-d'),
                    'ngay_het' => $ngayHet->format('Y-m-d'),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi checkThanhToan: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi server'], 500);
        }
    }

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

            $ngayHet = strtotime($goi->ngay_het);
            $ngayConLai = ceil(($ngayHet - time()) / 86400);
            if ($ngayConLai < 0) $ngayConLai = 0;

            if (now()->gt($goi->ngay_het)) {
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
     * LẤY THÔNG BÁO
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

            Log::info('getNotifications: User ID = ' . $user->id);

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
     * ĐÁNH DẤU ĐÃ ĐỌC
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
            if (!$goiId) {
                return response()->json(['message' => 'Thiếu gói dịch vụ ID'], 400);
            }
            Log::info('goi dich vu ID: ' . $goiId);

            $goi = DB::table('goidichvu')->where('id', $goiId)->first();
            if (!$goi) {
                return response()->json(['message' => 'Không tìm thấy gói dịch vụ'], 404);
            }

            $amount = $goi->gia ?? 0;
            if ($amount < 1000) {
                return response()->json(['message' => 'Giá gói dịch vụ không hợp lệ'], 400);
            }

            $orderCode = 'SP' . random_int(100000, 999999);

            $vnp_TmnCode    = config('vnpay.vnp_TmnCode');
            $vnp_HashSecret = config('vnpay.vnp_HashSecret');
            $vnp_Url        = config('vnpay.vnp_Url');
            $vnp_ReturnUrl  = config('vnpay.vnp_Returnurl', config('vnpay.vnp_ReturnUrl'));

            if (!$vnp_TmnCode || !$vnp_HashSecret || !$vnp_Url || !$vnp_ReturnUrl) {
                return response()->json([
                    'status' => 500,
                    'message' => 'Cấu hình VNPAY không hợp lệ!',
                ], 500);
            }

            $vnp_TxnRef    = $orderCode;
            $vnp_OrderInfo = 'Thanh toán gói dịch vụ ' . ($goi->ten_goi ?? '') . ' - ' . $vnp_TxnRef;
            $vnp_OrderType = 'other';
            $vnp_Amount    = (int) $amount * 100;
            $vnp_Locale    = 'vn';
            $vnp_IpAddr    = $request->ip();

            $inputData = [
                'vnp_Version'   => '2.1.0',
                'vnp_TmnCode'   => $vnp_TmnCode,
                'vnp_Amount'    => $vnp_Amount,
                'vnp_Command'   => 'pay',
                'vnp_CreateDate' => now()->format('YmdHis'),
                'vnp_CurrCode'  => 'VND',
                'vnp_IpAddr'    => $vnp_IpAddr,
                'vnp_Locale'    => $vnp_Locale,
                'vnp_OrderInfo' => $vnp_OrderInfo,
                'vnp_OrderType' => $vnp_OrderType,
                'vnp_ReturnUrl' => $vnp_ReturnUrl,
                'vnp_TxnRef'    => $vnp_TxnRef,
            ];
            $userData = [
                'user_id' => $user->id,
                'goiId' => $goiId
            ];
            ksort($inputData);
            $query    = http_build_query($inputData);
            $hashData = $query;
            $vnp_SecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

            $paymentUrl = $vnp_Url . '?' . $query . '&vnp_SecureHash=' . $vnp_SecureHash;

            // LƯU RIÊNG mapping order_code -> user_id, goi_id trong 30 phút
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
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server',
            ], 500);
        }
    }

    /**
     * VNPay return URL (xác thực, lưu DB và chuyển hướng về frontend)
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
                if ($i == 1) {
                    $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
                } else {
                    $hashData .= urlencode($key) . '=' . urlencode($value);
                    $i = 1;
                }
            }

            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

            $frontendUrl = env('URL_FRONTEND', 'http://localhost/HeThongChoThueSanTheThao/frontend/owner/vnpay_return');

            // Nếu hợp lệ và thành công, lấy mapping theo order_code để lưu DB
            if ($secureHash === $vnp_SecureHash && ($inputData['vnp_ResponseCode'] ?? '') === '00') {
                $orderCode = $inputData['vnp_TxnRef'] ?? null;
                $meta = $orderCode ? Cache::pull('vnp_order_' . $orderCode) : null;

                $userId = $meta['user_id'] ?? null;
                $goiId  = $meta['goi_id']  ?? null;

                // fallback nếu cần (trường hợp bạn vẫn đính kèm từ frontend)
                if (!$userId) $userId = $inputData['user_id'] ?? null;
                if (!$goiId)  $goiId  = $inputData['goiId'] ?? null;

                if ($userId && $goiId) {
                    $goi = DB::table('goidichvu')->where('id', $goiId)->first();
                    if ($goi) {
                        DB::table('goidamua')
                            ->where('nguoi_dung_id', $userId)
                            ->where('trang_thai', 'con_han')
                            ->update(['trang_thai' => 'het_han']);

                        $ngayMua = now();
                        $ngayHet = now()->addDays($goi->thoi_han ?? 30);

                        DB::table('goidamua')->insert([
                            'nguoi_dung_id' => $userId,
                            'goi_id'        => $goiId,
                            'ngay_mua'      => $ngayMua->format('Y-m-d'),
                            'ngay_het'      => $ngayHet->format('Y-m-d'),
                            'trang_thai'    => 'con_han'
                        ]);
                    } else {
                        Log::warning('vnpayReturn: Không tìm thấy gói ' . $goiId);
                    }
                } else {
                    Log::warning('vnpayReturn: Thiếu mapping user_id/goi_id cho order ' . ($orderCode ?? 'N/A'));
                }
            }

            if ($secureHash === $vnp_SecureHash) {
                return redirect($frontendUrl . '?' . http_build_query($request->all()));
            } else {
                return redirect($frontendUrl . '?status=fail&message=' . urlencode('Chữ ký không hợp lệ'));
            }
        } catch (\Throwable $e) {
            Log::error('VNPay vnpayReturn error: ' . $e->getMessage());
            $frontendUrl = env('URL_FRONTEND', 'http://localhost/HeThongChoThueSanTheThao/frontend/owner/vnpay_return');
            return redirect($frontendUrl . '?status=fail&message=' . urlencode('Lỗi server'));
        }
    }
}
