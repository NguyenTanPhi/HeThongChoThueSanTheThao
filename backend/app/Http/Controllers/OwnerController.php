<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

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
}