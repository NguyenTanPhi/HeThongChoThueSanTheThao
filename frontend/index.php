<?php
require_once 'config.php';

// Nếu đã đăng nhập → chuyển hướng theo role
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'] ?? 'customer';
    if ($role === 'admin') {
        header('Location: admin/dashboard.php');
        exit;
    } elseif ($role === 'owner') {
        header('Location: owner/quan-ly-san.php');
        exit;
    } else {
        header('Location: customer/home.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Sân Bóng Đá - Hệ thống đặt sân chuyên nghiệp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .container {
            max-width: 900px;
        }
        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            color: #333;
        }
        .card-header {
            background: linear-gradient(45deg, #1e3c72, #2a5298);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .card-header h1 {
            font-size: 2.8rem;
            font-weight: 800;
            margin: 0;
        }
        .card-header p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-top: 10px;
        }
        .card-body {
            padding: 50px 40px;
            text-align: center;
        }
        .logo {
            width: 120px;
            height: 120px;
            background: #2a5298;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(42, 82, 152, 0.4);
        }
        .logo i {
            font-size: 3.5rem;
            color: white;
        }
        .btn-login {
            background: linear-gradient(45deg, #1e3c72, #2a5298);
            color: white;
            padding: 15px 50px;
            font-size: 1.3rem;
            font-weight: bold;
            border-radius: 50px;
            border: none;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 10px 30px rgba(42, 82, 152, 0.4);
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background: linear-gradient(45deg, #2a5298, #1e3c72);
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(42, 82, 152, 0.5);
            color: white;
        }
        .features {
            margin-top: 40px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .feature {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            color: #333;
        }
        .feature i {
            font-size: 2.5rem;
            color: #2a5298;
            margin-bottom: 15px;
        }
        .feature h5 {
            font-weight: 700;
            margin-bottom: 10px;
        }
        .footer {
            text-align: center;
            padding: 20px;
            background: rgba(0, 0, 0, 0.1);
            font-size: 0.9rem;
            color: #ddd;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="logo">
                <i class="fas fa-futbol"></i>
            </div>
            <h1>ĐẶT SÂN BÓNG ĐÁ</h1>
            <p>Hệ thống đặt sân bóng chuyên nghiệp – Nhanh chóng – An toàn – Uy tín</p>
        </div>

        <div class="card-body">
            <p style="font-size: 1.3rem; margin-bottom: 30px; color: #555;">
                Tìm sân gần bạn • Đặt lịch chỉ 30 giây • Thanh toán online • Đánh giá thực tế
            </p>

            <a href="login.php" class="btn btn-login">
                ĐĂNG NHẬP NGAY
            </a>

            <div class="features">
                <div class="feature">
                    <i class="fas fa-map-marker-alt"></i>
                    <h5>Tìm sân nhanh</h5>
                    <p>Hơn 500 sân bóng tại 63 tỉnh thành</p>
                </div>
                <div class="feature">
                    <i class="fas fa-clock"></i>
                    <h5>Đặt sân 24/7</h5>
                    <p>Không cần gọi điện, đặt ngay trên web</p>
                </div>
                <div class="feature">
                    <i class="fas fa-mobile-alt"></i>
                    <h5>Thanh toán dễ dàng</h5>
                    <p>Momo, ZaloPay, thẻ ngân hàng</p>
                </div>
                <div class="feature">
                    <i class="fas fa-star"></i>
                    <h5>Đánh giá thực</h5>
                    <p>Từ hàng nghìn khách hàng đã đặt sân</p>
                </div>
            </div>
        </div>

        <div class="footer">
            © 2025 Hệ thống đặt sân bóng đá - Luận văn tốt nghiệp xuất sắc<br>
            Được xây dựng với Laravel 10 + Bootstrap 5
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>