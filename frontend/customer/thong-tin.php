<?php
require_once '../config.php';

// Chỉ customer được vào
requireRole('customer');

// Lấy thông tin người dùng hiện tại
$user = callAPI('GET', '/me', null, $_SESSION['token']);
if (!$user || !is_array($user)) {
    die('<div class="alert alert-danger">Không thể tải thông tin người dùng!</div>');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'name' => trim($_POST['name']),
        'email' => trim($_POST['email']),
        'phone' => trim($_POST['phone']),
    ];

    // Nếu có đổi mật khẩu
    if (!empty($_POST['password'])) {
        $payload['password'] = $_POST['password'];
    }

    $res = callAPI('PUT', '/update-profile', $payload, $_SESSION['token']);

    if ($res && empty($res['error'])) {
        $success = 'Cập nhật thông tin thành công!';
        // cập nhật lại dữ liệu hiển thị
        $user = callAPI('GET', '/me', null, $_SESSION['token']);
    } else {
        $error = $res['message'] ?? 'Cập nhật thất bại!';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông tin cá nhân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f8; font-family: 'Segoe UI', sans-serif; }
        .container { max-width: 600px; margin-top: 50px; }
        .card { border-radius: 10px; padding: 25px; background-color: #fff; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
        .btn-primary { background-color: #28a745; border: none; }
        .btn-primary:hover { background-color: #218838; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <h3 class="text-center mb-4">👤 Thông tin cá nhân</h3>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Họ và tên</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($user['name'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Số điện thoại</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
            </div>

            <hr>

            <div class="mb-3">
                <label class="form-label">Đổi mật khẩu (nếu muốn)</label>
                <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu mới (bỏ trống nếu không đổi)">
            </div>

            <button type="submit" class="btn btn-primary w-100">💾 Lưu thay đổi</button>
        </form>

        <a href="home.php" class="btn btn-secondary w-100 mt-3">⬅ Quay lại trang chủ</a>
    </div>
</div>
</body>
</html>
