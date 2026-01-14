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
            ->leftJoin('goidichvu', 'goidamua.goi_id', '=', 'goidichvu.id')
            ->where('goidamua.nguoi_dung_id', $user->id)
            ->orderByDesc('goidamua.ngay_mua')
            ->select( 'goidamua.goi_id', 'goidamua.gia',DB::raw("COALESCE(goidichvu.ten_goi, CONCAT('Gói đã xóa #', goidamua.goi_id)) AS ten_goi"), 'goidamua.ngay_mua', 'goidamua.ngay_het', 'goidamua.trang_thai')
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
                            'gia'           => $goi->gia,
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
    /**
 * Tạo link thanh toán ZaloPay cho việc mua gói dịch vụ (dành cho chủ sân)
 */
public function taoThanhToanGoiDichVuZaloPay(Request $request)
{
    try {
        // Log dữ liệu đầu vào để kiểm tra
        Log::info('ZaloPay taoThanhToanGoiDichVuZaloPay input', ['input' => $request->all()]);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'goi_dich_vu_id' => 'required|exists:goidichvu,id',
        ]);

        $goiId = $request->input('goi_dich_vu_id');
        $goi = DB::table('goidichvu')->where('id', $goiId)->first();

        if (!$goi) {
            return response()->json(['message' => 'Không tìm thấy gói dịch vụ'], 404);
        }

        $amount = $goi->gia ?? 0;
        if ($amount < 1000) {
            return response()->json(['message' => 'Giá trị thanh toán không hợp lệ'], 400);
        }
        $amount = (int)$amount; // Đảm bảo amount là số nguyên

        $config = config('zalo');
        if (empty($config['app_id']) || empty($config['key1'])) {
            return response()->json(['message' => 'Cấu hình ZaloPay chưa được thiết lập'], 500);
        }

        $transID = rand(100000, 999999);
        $appTransId = date("ymd") . "_" . $transID;

        $embeddata = json_encode([
            'user_id'    => $user->id,
            'goi_id'     => $goiId,
            'redirecturl' => env('ZALO_GOI_DICH_VU_RETURN_URL', 'http://localhost:5173/owner/zalo-goi-return'),
        ]);

        $items = json_encode([]); // luôn để mảng rỗng

        $order = [
            "app_id"        => $config["app_id"],
            "app_time"      => round(microtime(true) * 1000),
            "app_trans_id"  => $appTransId,
            "app_user"      => "owner_" . $user->id,
            "item"          => $items,
            "embed_data"    => $embeddata,
            "amount"        => $amount, // Đảm bảo là int
            "description"   => "Mua gói dịch vụ #{$goiId} - " . ($goi->ten_goi ?? 'Gói dịch vụ'),
            "bank_code"     => "",
        ];

        $data = $order["app_id"] . "|" . $order["app_trans_id"] . "|" . $order["app_user"] . "|" 
              . $order["amount"] . "|" . $order["app_time"] . "|" . $order["embed_data"] . "|" . $order["item"];

        $order["mac"] = hash_hmac("sha256", $data, $config["key1"]);

        // Log dữ liệu gửi sang ZaloPay để debug
        Log::info('ZaloPay order data', [
            'order' => $order,
            'data_string' => $data,
            'endpoint' => $config["endpoint"] ?? null
        ]);

        $zaloUrl = rtrim($config["endpoint"], '/') . '/v2/create';

        $context = stream_context_create([
            "http" => [
                "header"  => "Content-type: application/x-www-form-urlencoded\r\n",
                "method"  => "POST",
                "content" => http_build_query($order)
            ]
        ]);

        $resp = @file_get_contents($zaloUrl, false, $context);
        if ($resp === false) {
            $error = error_get_last();
            Log::error('ZaloPay tạo đơn gói dịch vụ thất bại', [
                'url' => $zaloUrl,
                'error' => $error ?? 'unknown'
            ]);
            return response()->json(['success' => false, 'message' => 'Không thể kết nối ZaloPay'], 500);
        }

        $result = json_decode($resp, true);

        Log::info('ZaloPay tạo đơn gói dịch vụ response', ['response' => $result]);

        if (isset($result['return_code']) && $result['return_code'] == 1) {
            // Lưu thông tin tạm vào cache (30 phút)
            Cache::put('zalo_goi_' . $appTransId, [
                'user_id' => $user->id,
                'goi_id'  => $goiId,
                'gia'     => $amount,
                'ten_goi' => $goi->ten_goi ?? 'Gói dịch vụ'
            ], now()->addMinutes(30));

            return response()->json([
                'success'     => true,
                'payment_url' => $result['order_url'] ?? $result['deeplink'] ?? '',
                'order_code'  => $appTransId,
                'method'      => 'zalo',
                'goi'         => [
                    'id'      => $goiId,
                    'ten_goi' => $goi->ten_goi,
                    'gia'     => $amount
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['return_message'] ?? 'Lỗi tạo đơn hàng ZaloPay'
        ], 400);

    } catch (\Throwable $e) {
        Log::error('Lỗi taoThanhToanGoiDichVuZaloPay: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json(['success' => false, 'message' => 'Lỗi server'], 500);
    }
}
/**
 * Xử lý callback & redirect từ ZaloPay khi mua gói dịch vụ
 */
public function zaloReturnGoiDichVu(Request $request)
{
    $result = [];
    $key2 = config('zalo.key2');
    $frontendUrl = env('URL_ZALO_GOI_FRONTEND_RETURN', 'http://localhost:5173/owner/zalo-goi-return');

    try {
        $postdata = file_get_contents('php://input');
        $postdatajson = json_decode($postdata, true);

        // Trường hợp redirect từ frontend (query string)
        if (empty($postdata) || !is_array($postdatajson)) {
            $params = $request->query();
            Log::info('ZaloPay redirect mua gói (frontend)', ['params' => $params]);

            if (($params['status'] ?? null) == '1' && !empty($params['apptransid'])) {
                $appTransId = $params['apptransid'];
                $meta = Cache::get('zalo_goi_' . $appTransId); // Đọc cache không xóa để debug
                Log::info('ZaloPay zaloReturnGoiDichVu meta (get)', [
                    'appTransId' => $appTransId,
                    'meta' => $meta
                ]);
                $metaPull = Cache::pull('zalo_goi_' . $appTransId); // Xóa cache như cũ
                Log::info('ZaloPay zaloReturnGoiDichVu meta (pull)', [
                    'appTransId' => $appTransId,
                    'meta' => $metaPull
                ]);

                $meta = $metaPull ?: $meta; // Ưu tiên metaPull nếu có

                if ($meta) {
                    $ngayMua = now();
                    $ngayHet = $ngayMua->addDays(DB::table('goidichvu')
                        ->where('id', $meta['goi_id'])
                        ->value('thoi_han') ?? 30);

                    // Tạo bản ghi gói đã mua
                    DB::table('goidamua')->insert([
                        'nguoi_dung_id' => $meta['user_id'],
                        'goi_id'        => $meta['goi_id'],
                        'gia'           => $meta['gia'],
                        'ngay_mua'      => $ngayMua->format('Y-m-d'),
                        'ngay_het'      => $ngayHet->format('Y-m-d'),
                        'trang_thai'    => 'con_han'
                    ]);

                    // Đánh dấu các gói cũ hết hạn (nếu cần)
                    DB::table('goidamua')
                        ->where('nguoi_dung_id', $meta['user_id'])
                        ->where('trang_thai', 'con_han')
                        ->where('ngay_het', '<', $ngayMua->format('Y-m-d'))
                        ->update(['trang_thai' => 'het_han']);
                } else {
                    Log::warning('ZaloPay zaloReturnGoiDichVu: Không tìm thấy meta trong cache cho appTransId ' . $appTransId);
                }
            }

            return redirect($frontendUrl . '?' . http_build_query($params));
        }

        // Callback từ server ZaloPay
        if (!isset($postdatajson["data"]) || !isset($postdatajson["mac"])) {
            $result["return_code"] = -1;
            $result["return_message"] = "invalid callback data";
            return response()->json($result);
        }

        $mac = hash_hmac("sha256", $postdatajson["data"], $key2);

        if (strcmp($mac, $postdatajson["mac"]) !== 0) {
            $result["return_code"] = -1;
            $result["return_message"] = "mac not equal";
            return response()->json($result);
        }

        $datajson = json_decode($postdatajson["data"], true);
        $appTransId = $datajson["app_trans_id"] ?? null;
        $meta = $appTransId ? Cache::pull('zalo_goi_' . $appTransId) : null;

        if ($meta) {
            $ngayMua = now();
            $ngayHet = $ngayMua->addDays(DB::table('goidichvu')
                ->where('id', $meta['goi_id'])
                ->value('thoi_han') ?? 30);

            DB::table('goidamua')->insert([
                'nguoi_dung_id' => $meta['user_id'],
                'goi_id'        => $meta['goi_id'],
                'gia'           => $datajson['amount'] ?? $meta['gia'],
                'ngay_mua'      => $ngayMua->format('Y-m-d'),
                'ngay_het'      => $ngayHet->format('Y-m-d'),
                'trang_thai'    => 'con_han'
            ]);

            // Cập nhật gói cũ hết hạn
            DB::table('goidamua')
                ->where('nguoi_dung_id', $meta['user_id'])
                ->where('trang_thai', 'con_han')
                ->where('ngay_het', '<', $ngayMua->format('Y-m-d'))
                ->update(['trang_thai' => 'het_han']);
        }

        $result["return_code"] = 1;
        $result["return_message"] = "success";

    } catch (\Exception $e) {
        $result["return_code"] = 0;
        $result["return_message"] = $e->getMessage();
        Log::error('ZaloPay callback mua gói lỗi: ' . $e->getMessage());
    }

    return response()->json($result);
}
public function checkThanhToan(Request $request, $orderCode)
{
    try {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Thử lấy meta từ cache (pull để tránh xử lý hai lần)
        $meta = Cache::pull('zalo_goi_' . $orderCode);
        // Nếu pull không có, thử get để debug/fallback
        if (!$meta) {
            $meta = Cache::get('zalo_goi_' . $orderCode);
        }

        Log::info('checkThanhToan zalo', [
            'orderCode' => $orderCode,
            'cache_meta' => $meta,
            'request_input' => $request->all(),
            'user_id' => $user->id
        ]);

        // Fallback dữ liệu từ request nếu cache không có
        $goiId = $meta['goi_id'] ?? $request->input('goi_dich_vu_id');
        $amount = $meta['gia'] ?? $request->input('amount');
        $userIdFromMeta = $meta['user_id'] ?? $user->id;

        if (!$goiId) {
            return response()->json(['success' => false, 'message' => 'Missing goi_dich_vu_id'], 400);
        }

        $goi = DB::table('goidichvu')->where('id', $goiId)->first();
        if (!$goi) {
            return response()->json(['success' => false, 'message' => 'Gói dịch vụ không tồn tại'], 404);
        }

        $ngayMua = now();
        $ngayHet = now()->addDays($goi->thoi_han ?? 30);

        // Tạo bản ghi gói đã mua
        DB::table('goidamua')->insert([
            'nguoi_dung_id' => $userIdFromMeta,
            'goi_id'        => $goiId,
            'gia'           => is_numeric($amount) ? (int)$amount : ($goi->gia ?? 0),
            'ngay_mua'      => $ngayMua->format('Y-m-d'),
            'ngay_het'      => $ngayHet->format('Y-m-d'),
            'trang_thai'    => 'con_han'
        ]);

        // Đánh dấu các gói cũ hết hạn
        DB::table('goidamua')
            ->where('nguoi_dung_id', $userIdFromMeta)
            ->where('trang_thai', 'con_han')
            ->where('ngay_het', '<', $ngayMua->format('Y-m-d'))
            ->update(['trang_thai' => 'het_han']);

        return response()->json(['success' => true, 'message' => 'Gói dịch vụ đã được tạo']);
    } catch (\Throwable $e) {
        Log::error('checkThanhToan error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json(['success' => false, 'message' => 'Lỗi server'], 500);
    }
}
}
