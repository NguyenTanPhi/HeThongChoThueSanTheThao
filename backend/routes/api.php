<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SanController;
use App\Http\Controllers\DatSanController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\GoiDichVuController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\CustomerSanController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [AuthController::class, 'me']);

    // Customer routes
    Route::get('/san', [SanController::class, 'index']);
    Route::get('/san/{id}', [SanController::class, 'show']);
    Route::post('/dat-san', [DatSanController::class, 'store']);
    Route::get('/lich-san/{san_id}/{ngay}', [DatSanController::class, 'lichTrong']);

    // Owner routes – ĐÃ ĐÚNG VỊ TRÍ
    Route::middleware('role:owner')->prefix('owner')->group(function () {
        Route::post('/san', [SanController::class, 'store']);
        Route::get('/my-san', [SanController::class, 'mySan']);

        // DI CHUYỂN VÀO ĐÂY – ĐÚNG!
        Route::get('/yeu-cau-thue', [DatSanController::class, 'danhSachChoDuyet']);
        Route::get('/notifications', [OwnerController::class, 'getNotifications']);
        Route::post('/notifications/{id}/read', [OwnerController::class, 'markNotificationRead']);
    }); // <-- ĐÓNG ĐÚNG
    // CÁC ROUTE SỬ DỤNG /san/{id} TRỰC TIẾP – KHÔNG CẦN /owner
    Route::middleware('role:owner')->group(function () {
        Route::put('/san/{id}', [SanController::class, 'update']);
        Route::delete('/san/{id}', [SanController::class, 'destroy']);
    });

    // Admin - Quản lý sân + users
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/san/cho-duyet', [AdminController::class, 'sanChoDuyet']);
        Route::post('/admin/san/{id}/duyet', [AdminController::class, 'duyetSan']);
        Route::get('/admin/users', [AdminController::class, 'users']);
    });

    // Chủ sân xem danh sách gói
    Route::get('/goi-dich-vu', [GoiDichVuController::class, 'index']);

    // Admin quản lý gói dịch vụ
    Route::prefix('admin')->group(function () {
        Route::get('/goi-dich-vu', [GoiDichVuController::class, 'index']);
        Route::post('/goi-dich-vu', [GoiDichVuController::class, 'store']);
        Route::put('/goi-dich-vu/{id}', [GoiDichVuController::class, 'update']);
        Route::delete('/goi-dich-vu/{id}', [GoiDichVuController::class, 'destroy']);
    });

    // Owner duyệt đặt sân
    Route::post('/owner/mua-goi', [OwnerController::class, 'muaGoi']);
    Route::post('/owner/check-thanh-toan/{orderId}', [OwnerController::class, 'checkThanhToan']);
    Route::get('/owner/goi-hien-tai', [OwnerController::class, 'goiHienTai']);

    // Chủ sân: quản lý lịch trống
    Route::prefix('owner')->group(function () {
        Route::get('/san/{id}/lich-trong', [SanController::class, 'getLichTrong']);
        Route::post('/san/{id}/lich-trong', [SanController::class, 'themLichTrong']);
        Route::put('/san/{id}/lich-trong/{lichId}', [SanController::class, 'suaLichTrong']);   
        Route::delete('/san/{id}/lich-trong/{lichId}', [SanController::class, 'xoaLichTrong']);
    });

    // Khách hàng: xem lịch trống
    Route::get('/san/{id}/lich-trong', [SanController::class, 'getLichTrongKhach']);

    // Customer routes
    Route::prefix('customer')->group(function () {
        Route::get('/san', [CustomerSanController::class, 'index']);
        Route::get('/san/{id}', [CustomerSanController::class, 'show']);
        Route::get('/san/{id}/lich-trong', [CustomerSanController::class, 'lichTrong']);
        Route::get('/notifications', [DatSanController::class, 'getNotifications']);
        Route::post('/mark-notification-read', [DatSanController::class, 'markNotificationRead']);
    });

    Route::put('/update-profile', [AuthController::class, 'updateProfile']);

}); // <-- ĐÓNG TOÀN BỘ auth:sanctum