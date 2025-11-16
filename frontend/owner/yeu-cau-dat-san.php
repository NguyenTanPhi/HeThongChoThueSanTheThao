<?php
require_once '../config.php';
requireRole('owner');

$user = getUser();

$response = callAPI('GET', '/owner/yeu-cau-cho-duyet', null, $_SESSION['token']);
$yeuCau = is_array($response) ? $response : [];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Yêu cầu đặt sân</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>Danh sách yêu cầu chờ duyệt</h3>
    <?php if(!empty($yeuCau)): ?>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Khách hàng</th>
                    <th>Sân</th>
                    <th>Ngày</th>
                    <th>Giờ</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($yeuCau as $yc): ?>
                <tr>
                    <td><?= htmlspecialchars($yc['user']['name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($yc['san']['ten_san'] ?? '-') ?></td>
                    <td><?= date('d/m/Y', strtotime($yc['ngay_dat'])) ?></td>
                    <td><?= substr($yc['gio_bat_dau'],0,5) ?> - <?= substr($yc['gio_ket_thuc'],0,5) ?></td>
                    <td><?= htmlspecialchars($yc['trang_thai']) ?></td>
                    <td>
                        <a href="duyet-dat-san.php?id=<?= $yc['id'] ?>&action=duyet" class="btn btn-success btn-sm">Duyệt</a>
                        <a href="duyet-dat-san.php?id=<?= $yc['id'] ?>&action=tu_choi" class="btn btn-danger btn-sm">Từ chối</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Chưa có yêu cầu nào chờ duyệt.</p>
    <?php endif; ?>
</div>
</body>
</html>
