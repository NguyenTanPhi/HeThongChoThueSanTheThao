<?php
require_once '../config.php';

// Chỉ owner mới được vào
requireRole('owner');

// Lấy thông tin người dùng hiện tại
$user = callAPI('GET', '/me', null, $_SESSION['token']);

if (!$user || !is_array($user)) {
    die('<div class="alert alert-danger text-center p-5">Không thể tải thông tin người dùng. Vui lòng đăng nhập lại!</div>');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate cơ bản
    if (empty($name) || empty($email)) {
        $error = 'Vui lòng nhập đầy đủ họ tên và email!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ!';
    } else {
        $payload = [
            'name'  => $name,
            'email' => $email,
            'phone' => $phone,
        ];

        // Chỉ gửi password nếu người dùng nhập
        if (!empty($password)) {
            $payload['password'] = $password;
        }

        $res = callAPI('PUT', '/update-profile', $payload, $_SESSION['token']);

        if ($res && empty($res['error'])) {
            $success = 'Cập nhật thông tin thành công!';
            // Cập nhật lại dữ liệu hiển thị
            $user = callAPI('GET', '/me', null, $_SESSION['token']);
        } else {
            $error = $res['message'] ?? $res['error'] ?? 'Cập nhật thất bại, vui lòng thử lại!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thông tin cá nhân - Chủ sân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #fff8f0 0%, #ffe0b3 100%);
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(253,126,20,0.15);
            border: none;
            background: white;
        }
        .form-control:focus {
            border-color: #fd7e14;
            box-shadow: 0 0 0 0.25rem rgba(253,126,20,0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #fd7e14, #e67e22);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: bold;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #e67e22, #d35400);
        }
        .btn-secondary {
            border-radius: 12px;
            padding: 12px;
        }
        .avatar-preview {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #fd7e14;
        }
    </style>
</head>
<body class="pt-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card p-5">
                <div class="text-center mb-4">
                    <img src="<?= $user['avatar'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=fd7e14&color=fff&size=120' ?>" 
                         alt="Avatar" class="avatar-preview mb-3">
                    <h3 class="fw-bold text-dark">Thông tin cá nhân</h3>
                    <p class="text-muted">Chủ sân: <strong><?= htmlspecialchars($user['name']) ?></strong></p>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle"></i> <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Họ và tên</label>
                        <input type="text" name="name" class="form-control form-control-lg" 
                               value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control form-control-lg" 
                               value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control form-control-lg" 
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>

                    <hr class="my-4">

                    <div class="mb-4">
                        <label class="form-label fw-bold">Đổi mật khẩu mới</label>
                        <input type="password" name="password" class="form-control form-control-lg" 
                               placeholder="Để trống nếu không muốn đổi mật khẩu">
                        <small class="text-muted">Mật khẩu phải ít nhất 6 ký tự</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                        Lưu thay đổi
                    </button>

                    <a href="quan-ly-san.php" class="btn btn-secondary btn-lg w-100">
                        Quay lại quản lý sân
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>