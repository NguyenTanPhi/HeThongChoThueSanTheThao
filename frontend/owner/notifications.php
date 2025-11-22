<?php
require_once '../config.php';
requireRole('owner');
$user = getUser();

// LẤY THÔNG BÁO QUA API (AN TOÀN – KHÔNG LỖI DB)
$response = callAPI('GET', '/owner/notifications', null, $_SESSION['token']);

if (isset($response['error'])) {
    $notifications = [];
    $apiError = $response['message'] ?? 'Lỗi kết nối API';
} else {
    $notifications = $response['notifications'] ?? $response; // tùy cấu trúc trả về
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thông báo - Chủ sân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {background:linear-gradient(135deg,#f5f7fa,#c3cfe2);font-family:'Segoe UI',sans-serif;min-height:100vh}
        .header {background:linear-gradient(135deg,#667eea,#764ba2);color:white;padding:2.5rem 0}
        .noti-card {border:none;border-radius:18px;box-shadow:0 8px 30px rgba(0,0,0,.12);transition:.3s;margin-bottom:1rem}
        .noti-card:hover {transform:translateY(-6px);box-shadow:0 15px 40px rgba(102,126,234,.25)}
        .unread {background:#e8f5e8 !important;border-left:6px solid #28a745}
        .read {background:#f8f9fa}
        .badge-new {background:#e53935;color:white;font-size:0.7rem;padding:4px 8px}
    </style>
</head>
<body>

<div class="header text-center">
    <div class="container">
        <h2><i class="bi bi-bell-fill"></i> Thông báo của tôi</h2>
        <p class="mb-0 opacity-80">Tất cả thông báo về sân bóng và đánh giá khách hàng</p>
    </div>
</div>

<div class="container my-5">
    <?php if (!empty($apiError)): ?>
        <div class="alert alert-danger text-center">
            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($apiError) ?>
        </div>
    <?php elseif (empty($notifications)): ?>
        <div class="text-center py-5">
            <i class="bi bi-bell-slash" style="font-size:5rem;color:#ddd"></i>
            <h4 class="mt-4 text-muted">Chưa có thông báo nào</h4>
            <p class="text-muted">Khi có đánh giá mới hoặc sân được duyệt, bạn sẽ nhận thông báo tại đây!</p>
        </div>
    <?php else: ?>
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <?php foreach ($notifications as $noti): ?>
                    <div class="card noti-card <?= $noti['da_doc'] == 0 ? 'unread' : 'read' ?>">
                        <div class="card-body p-4">
                            <div class="d-flex">
                                <div class="me-3">
                                    <?php if (strpos($noti['noi_dung'] ?? '', 'đánh giá') !== false): ?>
                                        <i class="bi bi-star-fill text-warning" style="font-size:2.2rem"></i>
                                    <?php elseif (strpos($noti['noi_dung'] ?? '', 'duyệt') !== false): ?>
                                        <i class="bi bi-check-circle-fill text-success" style="font-size:2.2rem"></i>
                                    <?php else: ?>
                                        <i class="bi bi-bell-fill text-primary" style="font-size:2.2rem"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-2 fw-bold">
                                        <?= htmlspecialchars($noti['noi_dung'] ?? 'Thông báo mới') ?>
                                        <?php if ($noti['da_doc'] == 0): ?>
                                            <span class="badge-new rounded-pill ms-2">MỚI</span>
                                        <?php endif; ?>
                                    </h5>

                                    <?php if (!empty($noti['ly_do'])): ?>
                                        <div class="bg-light p-3 rounded mt-2">
                                            <small class="text-muted">Chi tiết đánh giá:</small><br>
                                            <p class="mb-0"><?= nl2br(htmlspecialchars($noti['ly_do'])) ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <small class="text-muted d-block mt-3">
                                        <i class="bi bi-clock"></i>
                                        <?= date('d/m/Y H:i', strtotime($noti['created_at'] ?? now())) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="quan-ly-san.php" class="btn btn-outline-primary rounded-pill px-5">
            <i class="bi bi-arrow-left"></i> Quay lại quản lý sân
        </a>
    </div>
</div>

</body>
</html>