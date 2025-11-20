<?php
require_once '../config.php';
requireRole('customer');

// Lấy thông tin người dùng hiện tại
$user = callAPI('GET', '/me', null, $_SESSION['token']);
if (!$user || !is_array($user)) {
    die('<div class="alert alert-danger text-center">Không thể tải thông tin người dùng!</div>');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'name'  => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
    ];

    if (!empty($_POST['password'])) {
        $payload['password'] = $_POST['password'];
    }

    $res = callAPI('PUT', '/update-profile', $payload, $_SESSION['token']);

    if ($res && empty($res['error'])) {
        $success = 'Cập nhật thông tin thành công!';
        $user = callAPI('GET', '/me', null, $_SESSION['token']); // Cập nhật lại dữ liệu
    } else {
        $error = $res['message'] ?? 'Cập nhật thất bại. Vui lòng thử lại!';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông tin cá nhân</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
        }
        header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 2.5rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        nav {
            background-color: #ecf0f1;
            padding: 15px 0;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        nav a {
            margin: 0 30px;
            font-weight: 600;
            color: #2c3e50;
            text-decoration: none;
            font-size: 1.1rem;
            transition: color 0.3s;
        }
        nav a:hover { color: #3498db; }

        .profile-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            margin: 2rem auto;
            max-width: 600px;
            transition: transform 0.4s;
        }
        .profile-card:hover {
            transform: translateY(-10px);
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-size: 3.5rem;
            font-weight: bold;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 1rem;
        }
        .btn-save {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        .btn-save:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.4);
        }
        .btn-back {
            background: #6c757d;
            border-radius: 12px;
            padding: 14px;
            font-weight: bold;
        }
        .alert {
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header>
    <div class="container">
        <h1 class="text-center mb-0">Thông tin cá nhân</h1>
    </div>
</header>

<!-- MENU -->
<nav>
    <a href="home.php">Trang chủ</a>
    <a href="lich-su-dat.php">Lịch sử đặt sân</a>
    <a href="../logout.php">Đăng xuất</a>
</nav>

<div class="container">
    <div class="profile-card">
        <!-- Avatar -->
        <div class="profile-avatar">
            <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
        </div>

        <h4 class="text-center mb-4 text-primary">
            Xin chào, <strong><?= htmlspecialchars($user['name'] ?? '') ?></strong>!
        </h4>

        <!-- Thông báo -->
        <?php if ($success): ?>
            <div class="alert alert-success text-center">
                <i class="bi bi-check-circle-fill"></i> <?= $success ?>
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger text-center">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- Form cập nhật -->
        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold text-dark">Họ và tên</label>
                <input type="text" name="name" class="form-control form-control-lg" required 
                       value="<?= htmlspecialchars($user['name'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold text-dark">Email</label>
                <input type="email" name="email" class="form-control form-control-lg" required 
                       value="<?= htmlspecialchars($user['email'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold text-dark">Số điện thoại</label>
                <input type="text" name="phone" class="form-control form-control-lg" 
                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Ví dụ: 0901234567">
            </div>

            <hr class="my-4">

            <div class="mb-4">
                <label class="form-label fw-bold text-warning">Đổi mật khẩu mới (tùy chọn)</label>
                <input type="password" name="password" class="form-control form-control-lg" 
                       placeholder="Để trống nếu không muốn đổi mật khẩu">
                <small class="text-muted">Mật khẩu phải ít nhất 6 ký tự</small>
            </div>

            <div class="d-grid gap-3">
                <button type="submit" class="btn btn-success btn-save text-white">
                    Lưu thay đổi
                </button>
                <a href="home.php" class="btn btn-secondary btn-back">
                    Quay lại trang chủ
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>