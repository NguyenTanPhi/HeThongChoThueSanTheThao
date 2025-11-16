<?php
require_once '../config.php';

// Nếu đã login → chuyển về trang quản lý
if (isset($_SESSION['user'])) {
    header('Location: quan-ly-san.php');
    exit;
}

$error = '';
$success = '';

if ($_POST) {
    $data = [
        'name' => trim($_POST['name']),
        'email' => trim($_POST['email']),
        'phone' => trim($_POST['phone']),
        'password' => $_POST['password'],
        'password_confirmation' => $_POST['password_confirmation'],
        'role' => 'owner'  // CHECKMARK BẮT BUỘC LÀ OWNER – KHÔNG THỂ NHẦM!
    ];

    // CHECKMARK GỌI ĐÚNG API – KHÔNG DÙNG FULL URL
    $response = callAPI('POST', '/register', $data);

    // CHECKMARK KIỂM TRA KỸ: PHẢI CÓ USER + ROLE = OWNER
    if (isset($response['user']) && $response['user']['role'] === 'owner') {
        $success = 'Đăng ký chủ sân thành công! Đang chuyển về trang đăng nhập...';
        
        // CHECKMARK TỰ ĐỘNG CHUYỂN VỀ LOGIN SAU 2 GIÂY
        echo "<script>
                setTimeout(function() {
                    window.location.href = '../login.php';
                }, 2000);
              </script>";
    } else {
        // CHECKMARK HIỂN THỊ LỖI CHI TIẾT TỪ LARAVEL
        $error = $response['message'] ?? 'Đăng ký thất bại!';
        if (isset($response['errors'])) {
            $error .= '<ul class="mt-2 mb-0">';
            foreach ($response['errors'] as $field => $messages) {
                foreach ($messages as $msg) {
                    $error .= "<li><small>$msg</small></li>";
                }
            }
            $error .= '</ul>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký Chủ sân - Đặt Sân Bóng Đá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #fd7e14 0%, #ff9a3d 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
            max-width: 520px;
            width: 100%;
        }
        .card-header {
            background: linear-gradient(45deg, #fd7e14, #e67e22);
            color: white;
            padding: 45px 20px;
            text-align: center;
        }
        .card-header h2 {
            font-size: 2.3rem;
            font-weight: 800;
            margin: 0;
        }
        .badge-vip {
            background: #ffc107;
            color: #212529;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 0.95rem;
            display: inline-block;
            margin-top: 12px;
            box-shadow: 0 4px 15px rgba(255,193,7,0.4);
        }
        .card-body {
            padding: 45px;
        }
        .logo {
            width: 95px;
            height: 95px;
            background: white;
            border-radius: 50%;
            margin: -75px auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25);
        }
        .logo i {
            font-size: 3rem;
            color: #fd7e14;
        }
        .form-control {
            border-radius: 50px;
            padding: 15px 22px;
            font-size: 1.1rem;
            border: 2.5px solid #e0e0e0;
        }
        .form-control:focus {
            border-color: #fd7e14;
            box-shadow: 0 0 0 0.25rem rgba(253, 126, 20, 0.3);
        }
        .input-group-text {
            border-radius: 50px 0 0 50px;
            background: #fd7e14;
            color: white;
            border: none;
            font-size: 1.1rem;
        }
        .btn-register {
            background: linear-gradient(45deg, #fd7e14, #e67e22);
            color: white;
            padding: 16px;
            font-size: 1.3rem;
            font-weight: bold;
            border-radius: 50px;
            border: none;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(253, 126, 20, 0.4);
        }
        .btn-register:hover {
            background: linear-gradient(45deg, #e67e22, #fd7e14);
            transform: translateY(-4px);
            box-shadow: 0 15px 40px rgba(253, 126, 20, 0.6);
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 18px;
            border-radius: 12px;
            text-align: center;
            font-size: 1.15rem;
            font-weight: bold;
            border: 1px solid #c3e6cb;
        }
        .back-home {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: #6c757d;
            font-size: 0.95rem;
            text-decoration: none;
        }
        .back-home:hover {
            color: #fd7e14;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="register-card">
        <div class="card-header">
            <h2>ĐĂNG KÝ CHỦ SÂN</h2>
            <p>Quản lý sân bóng – Kiếm tiền dễ dàng – Hỗ trợ 24/7</p>
            <span class="badge-vip">MIỄN PHÍ ĐĂNG SÂN ĐẦU TIÊN</span>
        </div>

        <div class="card-body">
            <div class="logo">
                <i class="fas fa-futbol"></i>
            </div>

            <?php if ($success): ?>
                <div class="success-message">
                    Checkmark <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <strong>Lỗi:</strong> <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                        <input type="text" name="name" class="form-control" placeholder="Họ tên chủ sân" value="<?= $_POST['name'] ?? '' ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="Email" value="<?= $_POST['email'] ?? '' ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="text" name="phone" class="form-control" placeholder="Số điện thoại" value="<?= $_POST['phone'] ?? '' ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Nhập lại mật khẩu" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-register">
                    ĐĂNG KÝ CHỦ SÂN NGAY
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="../register.php" style="color:#28a745; font-weight:bold;">
                    Bạn là khách hàng? Đăng ký tại đây
                </a>
            </div>
            <?php endif; ?>

            <a href="../index.php" class="back-home">
                Checkmark Quay lại trang chủ
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>