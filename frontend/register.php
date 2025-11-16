<?php
require_once 'config.php';

// Nếu đã đăng nhập → không cho vào trang đăng ký
if (isset($_SESSION['user'])) {
    header('Location: customer/home.php');
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
        'role' => 'customer'
    ];

    $response = callAPI('POST', '/register', $data);

    if (isset($response['user'])) {
        // KHÔNG LƯU SESSION, KHÔNG TỰ ĐỘNG ĐĂNG NHẬP
        $success = 'Đăng ký thành công! Vui lòng đăng nhập để tiếp tục.';
        
        // CHUYỂN VỀ TRANG LOGIN SAU 2 GIÂY
        echo "<script>
                setTimeout(() => { 
                    window.location.href = 'login.php'; 
                }, 2000);
              </script>";
    } else {
        $error = $response['message'] ?? 'Đăng ký thất bại. Vui lòng thử lại!';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký Khách hàng - Đặt Sân Bóng Đá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
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
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
        }
        .card-header {
            background: linear-gradient(45deg, #11998e, #38ef7d);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .card-header h2 {
            font-size: 2.2rem;
            font-weight: 800;
            margin: 0;
        }
        .card-header p {
            margin-top: 10px;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        .card-body {
            padding: 40px;
        }
        .logo {
            width: 90px;
            height: 90px;
            background: white;
            border-radius: 50%;
            margin: -70px auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        .logo i {
            font-size: 2.8rem;
            color: #11998e;
        }
        .form-control {
            border-radius: 50px;
            padding: 14px 20px;
            font-size: 1.1rem;
            border: 2px solid #e0e0e0;
        }
        .form-control:focus {
            border-color: #11998e;
            box-shadow: 0 0 0 0.2rem rgba(17, 153, 142, 0.25);
        }
        .input-group-text {
            border-radius: 50px 0 0 50px;
            background: #11998e;
            color: white;
            border: none;
        }
        .btn-register {
            background: linear-gradient(45deg, #11998e, #38ef7d);
            color: white;
            padding: 15px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 50px;
            border: none;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .btn-register:hover {
            background: linear-gradient(45deg, #38ef7d, #11998e);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(17, 153, 142, 0.4);
        }
        .success-message {
            text-align: center;
            font-size: 1.1rem;
            color: #155724;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 0.95rem;
        }
        .login-link a {
            color: #11998e;
            font-weight: bold;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .owner-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            padding: 14px;
            background: #fd7e14;
            color: white;
            border-radius: 50px;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .owner-link:hover {
            background: #e67e22;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(253, 126, 20, 0.4);
            color: white;
        }
        .back-home {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #95a5a6;
            font-size: 0.95rem;
            text-decoration: none;
        }
        .back-home:hover {
            color: #11998e;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="register-card">
        <div class="card-header">
            <h2>ĐĂNG KÝ KHÁCH HÀNG</h2>
            <p>Đặt sân nhanh chóng – Nhận ưu đãi liền tay!</p>
        </div>

        <div class="card-body">
            <div class="logo">
                <i class="fas fa-futbol"></i>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success-message">
                    <strong>Checkmark <?= $success ?></strong>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="name" class="form-control" placeholder="Họ và tên" required>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="text" name="phone" class="form-control" placeholder="Số điện thoại" required>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Nhập lại mật khẩu" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-register">
                    ĐĂNG KÝ NGAY
                </button>
            </form>

            <div class="login-link">
                Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
            </div>

            <a href="owner/register.php" class="owner-link">
                BẠN LÀ CHỦ SÂN? ĐĂNG KÝ TẠI ĐÂY
            </a>
            <?php endif; ?>

            <a href="index.php" class="back-home">
                Checkmark Quay lại trang chủ
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>