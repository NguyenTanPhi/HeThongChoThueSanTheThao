<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SanController;
use App\Http\Controllers\DatSanController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\GoiDichVuController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\CustomerSanController;
use App\Http\Controllers\DanhGiaController;




// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/owner/confirm', [AuthController::class, 'confirmOwner']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);


// Route VNPay return
Route::get('/vnpay_return', [OwnerController::class, 'vnpayReturn']);
Route::get('/customer/vnpay_return', [DatSanController::class, 'vnpayReturnDatSan']);

// Route ZaloPay return

//Route::get('/zalo_return', [DatSanController::class, 'zaloRedirectReturn']);
Route::get('/zalo_return', [DatSanController::class, 'zaloReturnDatSan']);

// Protected routes
Route::get('/san', [SanController::class, 'index']);
  Route::get('/san/{id}', [SanController::class, 'show']);
  Route::get('/danh-gia/san/{san_id}', [DanhGiaController::class, 'getBySan']);
  Route::get('/san/{id}/lich-trong', [SanController::class, 'getLichTrongKhach']);
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/admin/bao-cao/dat-san', [AdminReportController::class, 'baoCaoDatSan']);
    Route::get('/admin/bao-cao/goi-dich-vu', [AdminReportController::class, 'baoCaoGoiDichVu']);
    Route::get('/danh-gia/check', [DanhGiaController::class, 'checkDaDanhGia']);
    Route::post('/danh-gia', [DanhGiaController::class, 'store']);
    

    // Customer routes
    
  
    Route::post('/dat-san', [DatSanController::class, 'store']);
    Route::get('/lich-san/{san_id}/{ngay}', [DatSanController::class, 'lichTrong']);
    Route::get('/customer/dat-san', [DatSanController::class, 'customerMyBookings']);

    // Owner routes
    Route::middleware('role:owner')->prefix('owner')->group(function () {
        Route::post('/san', [SanController::class, 'store']);
        Route::get('/my-san', [SanController::class, 'mySan']);
        Route::get('/lich-su-dat', [DatSanController::class, 'lichSuDat']);
        Route::get('/thong-ke', [DatSanController::class, 'thongKe']);
        Route::get('/notifications', [OwnerController::class, 'getNotifications']);
        Route::post('/notifications/{id}/read', [OwnerController::class, 'markNotificationRead']);
    });

   
    Route::middleware('role:owner')->group(function () {
        Route::put('/san/{id}', [SanController::class, 'update']);
        Route::delete('/san/{id}', [SanController::class, 'destroy']);
    });

    // Admin - Quản lý sân + users
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/san/cho-duyet', [AdminController::class, 'sanChoDuyet']);
        Route::post('/admin/san/{id}/duyet', [AdminController::class, 'duyetSan']);
        Route::get('/admin/users', [AdminController::class, 'users']);
        Route::put('/admin/user/{id}/status', [AdminController::class, 'updateUserStatus']);
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


 
    Route::get('/owner/goi-hien-tai', [OwnerController::class, 'goiHienTai']);
    Route::post('/owner/thanh-toan', [OwnerController::class, 'taoThanhToan']);

    // Chủ sân: quản lý lịch trống
    Route::prefix('owner')->group(function () {
        Route::get('/san/{id}/lich-trong', [SanController::class, 'getLichTrong']);
        Route::post('/san/{id}/lich-trong', [SanController::class, 'themLichTrong']);
        Route::put('/san/{id}/lich-trong/{lichId}', [SanController::class, 'suaLichTrong']);
        Route::delete('/san/{id}/lich-trong/{lichId}', [SanController::class, 'xoaLichTrong']);
    });

   
    

    // Customer routes
    Route::prefix('customer')->group(function () {
        Route::get('/san', [CustomerSanController::class, 'index']);
        Route::get('/san/{id}', [CustomerSanController::class, 'show']);
        Route::get('/san/{id}/lich-trong', [CustomerSanController::class, 'lichTrong']);
        Route::post('/dat-san-thanh-toan', [DatSanController::class, 'taoThanhToanDatSan']);
    });

    Route::put('/update-profile', [AuthController::class, 'updateProfile']);
});