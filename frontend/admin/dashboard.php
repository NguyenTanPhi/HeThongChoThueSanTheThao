<?php
require_once '../config.php';
requireRole('admin');
$user = getUser();

// ĐẾM SỐ SÂN CHỜ DUYỆT (REALTIME)
$pendingCount = 0;
$response = callAPI('GET', '/admin/san/cho-duyet', null, $_SESSION['token']);
if ($response && is_array($response)) {
    $pendingCount = count($response);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Quản trị hệ thống</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }
        .container {
            max-width: 1000px;
        }
        .header {
            background: linear-gradient(45deg, #1e3799, #4a69bd);
            color: white;
            padding: 30px;
            border-radius: 20px 20px 0 0;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .header h1 {
            margin: 0;
            font-weight: 800;
            font-size: 2.5rem;
            text-shadow: 0 3px 10px rgba(0,0,0,0.4);
        }
        .menu-card {
            background: white;
            border-radius: 0 0 20px 20px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }
        .menu-item {
            background: linear-gradient(45deg, #f093fb, #f5576c);
            color: white;
            padding: 25px;
            border-radius: 18px;
            margin-bottom: 20px;
            text-align: center;
            transition: all 0.4s;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(240,147,251,0.4);
        }
        .menu-item:hover {
            transform: translateY(-12px) scale(1.05);
            box-shadow: 0 20px 40px rgba(240,147,251,0.6);
        }
        .menu-item i {
            font-size: 3.5rem;
            margin-bottom: 15px;
            display: block;
        }
        .menu-item .badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #ff3e4d;
            font-size: 1rem;
            padding: 8px 12px;
            border-radius: 50px;
            font-weight: bold;
        }
        .menu-item a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.3rem;
        }
        .menu-item a:hover {
            text-shadow: 0 0 10px rgba(255,255,255,0.8);
        }
        .logout-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #e74c3c;
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: bold;
            text-decoration: none;
            box-shadow: 0 8px 20px rgba(231,76,60,0.5);
            z-index: 9999;
            transition: all 0.3s;
        }
        .logout-btn:hover {
            background: #c0392b;
            transform: scale(1.1);
            color: white;
        }
        .welcome {
            font-size: 1.3rem;
            margin-top: 10px;
            opacity: 0.95;
        }
    </style>
</head>
<body>


<a href="../logout.php" class="logout-btn">
    ĐĂNG XUẤT
</a>

<div class="container">
    <div class="header">
        <h1>QUẢN TRỊ VIÊN</h1>
        <p class="welcome">Xin chào <strong><?= htmlspecialchars($user['name']) ?></strong>! Chúc bạn một ngày làm việc hiệu quả!</p>
    </div>

    <div class="menu-card">
        <!-- MỤC MỚI: QUẢN LÝ GÓI DỊCH VỤ -->
        <div class="menu-item" style="background: linear-gradient(45deg, #4facfe, #00f2fe);">
            <i class="fas fa-gift"></i>
            <span class="badge">MỚI</span>
            <a href="goi-dich-vu.php">QUẢN LÝ GÓI DỊCH VỤ</a>
            <p style="margin: 10px 0 0; font-size: 0.9rem; opacity: 0.9;">
                Tạo VIP, Pro, Basic cho chủ sân
            </p>
        </div>

        <!-- DUYỆT SÂN MỚI -->
        <div class="menu-item">
            <i class="fas fa-futbol"></i>
            <?php if ($pendingCount > 0): ?>
                <span class="badge"><?= $pendingCount ?></span>
            <?php endif; ?>
            <a href="san-cho-duyet.php">DUYỆT SÂN MỚI</a>
            <p style="margin: 10px 0 0; font-size: 0.9rem; opacity: 0.9;">
                <?= $pendingCount > 0 ? "$pendingCount sân đang chờ duyệt" : "Không có sân chờ duyệt" ?>
            </p>
        </div>

        <!-- QUẢN LÝ NGƯỜI DÙNG -->
        <div class="menu-item" style="background: linear-gradient(45deg, #a8e6cf, #66bb6a);">
            <i class="fas fa-users"></i>
            <a href="quan-ly-user.php">QUẢN LÝ NGƯỜI DÙNG</a>
            <p style="margin: 10px 0 0; font-size: 0.9rem; opacity: 0.9;">
                Khóa, mở, xem thông tin tài khoản
            </p>
        </div>

        <!-- THÊM MỤC MỚI: BÁO CÁO -->
        <div class="menu-item" style="background: linear-gradient(45deg, #ff9a9e, #fad0c4);">
            <i class="fas fa-chart-pie"></i>
            <a href="bao-cao.php">BÁO CÁO DOANH THU</a>
            <p style="margin: 10px 0 0; font-size: 0.9rem; opacity: 0.9;">
                Thống kê đặt sân, gói dịch vụ
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php 
// Bỏ dòng require check-login.php vì đã có requireRole('admin') ở trên
// require_once 'check-login.php'; 
?>