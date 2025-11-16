<?php
session_start();
if ($_POST) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $url = "http://127.0.0.1:8000/api/login";
    $data = json_encode(['email' => $email, 'password' => $password]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        $res = json_decode($response, true);
        $_SESSION['token'] = $res['token'];
        $_SESSION['user'] = $res['user'];

        $role = $res['user']['role'];
        if ($role == 'admin') {
            header('Location: admin/dashboard.php');
        } elseif ($role == 'owner') {
            header('Location: owner/quan-ly-san.php');
        } else {
            header('Location: customer/home.php');
        }
        exit;
    } else {
        $error = json_decode($response, true);
        $msg = $error['message'] ?? 'Lỗi kết nối server';
        echo "<script>alert('Đăng nhập thất bại: $msg');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống đặt sân bóng đá</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            max-width: 480px;
            width: 100%;
        }
        .card-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
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
            color: #667eea;
        }
        .form-control {
            border-radius: 50px;
            padding: 14px 20px;
            font-size: 1.1rem;
            border: 2px solid #e0e0e0;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .input-group-text {
            border-radius: 50px 0 0 50px;
            background: #667eea;
            color: white;
            border: none;
        }
        .btn-login {
            background: linear-gradient(45deg, #667eea, #764ba2);
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
        .btn-login:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        .register-btn {
            display: block;
            text-align: center;
            padding: 14px;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.1rem;
            margin: 12px 0;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-customer {
            background: #28a745;
            color: white;
        }
        .btn-owner {
            background: #fd7e14;
            color: white;
        }
        .btn-customer:hover, .btn-owner:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            color: white;
        }
        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
            color: #95a5a6;
        }
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #ddd;
        }
        .divider span {
            background: white;
            padding: 0 15px;
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
            color: #667eea;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="login-card">
        <div class="card-header">
            <h2>ĐĂNG NHẬP</h2>
            <p>Chào mừng bạn đến với hệ thống đặt sân bóng đá</p>
        </div>

        <div class="card-body">
            <div class="logo">
                <i class="fas fa-futbol"></i>
            </div>

            <form method="POST">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Ghi nhớ</label>
                    </div>
                    <a href="#" style="color:#dc3545; font-size:0.9rem;">Quên mật khẩu?</a>
                </div>

                <button type="submit" class="btn btn-login">
                    ĐĂNG NHẬP
                </button>
            </form>

            <div class="divider"><span>HOẶC</span></div>

            <a href="register.php" class="register-btn btn-customer">
                ĐĂNG KÝ KHÁCH HÀNG
            </a>

            <a href="owner/register.php" class="register-btn btn-owner">
                ĐĂNG KÝ CHỦ SÂN
            </a>

            <a href="index.php" class="back-home">
                Quay lại trang chủ
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>