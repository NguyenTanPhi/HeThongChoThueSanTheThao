<?php
require_once '../config.php';
requireRole('customer');

$san_id = $_GET['san_id'] ?? 0;
$ngay = $_GET['ngay'] ?? '';
$gio_bat_dau = $_GET['gio_bat_dau'] ?? '';
$gio_ket_thuc = $_GET['gio_ket_thuc'] ?? '';
$gia = $_GET['gia'] ?? 0;

if (!$san_id || !$ngay || !$gio_bat_dau || !$gio_ket_thuc) {
    die("Thiếu thông tin thanh toán!");
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Giả lập thanh toán thành công
    $thanhToanThanhCong = true;

    if ($thanhToanThanhCong) {

        // Gọi API tạo đặt sân
        $payload = [
            'san_id' => $san_id,
            'ngay_dat' => $ngay,
            'gio_bat_dau' => $gio_bat_dau,
            'gio_ket_thuc' => $gio_ket_thuc,
            'tong_gia' => $gia,
        ];

        $res = callAPI('POST', '/dat-san', $payload, $_SESSION['token']);

        if (isset($res['dat_san'])) {
            $success = "Thanh toán thành công! Bạn đã đặt sân thành công.";
        } else {
            $error = "Thanh toán thành công nhưng tạo đặt sân thất bại!";
        }
    } else {
        $error = "Thanh toán thất bại!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Thanh toán sân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <a class="btn btn-primary" href="chi-tiet-san.php?id=<?= $san_id ?>">Quay lại sân</a>
        <?php exit; ?>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <a class="btn btn-secondary" href="dat-san.php?san_id=<?= $san_id ?>&ngay=<?= $ngay ?>&gio_bat_dau=<?= $gio_bat_dau ?>&gio_ket_thuc=<?= $gio_ket_thuc ?>&gia=<?= $gia ?>">Thử lại</a>
    <?php endif; ?>

    <h3>Thanh toán đặt sân</h3>
    <p>Sân ID: <?= $san_id ?></p>
    <p>Ngày: <?= date('d/m/Y', strtotime($ngay)) ?></p>
    <p>Giờ: <?= substr($gio_bat_dau,0,5) ?> - <?= substr($gio_ket_thuc,0,5) ?></p>
    <p>Tổng tiền: <b><?= number_format($gia) ?>₫</b></p>

    <form method="post">
        <button type="submit" class="btn btn-success w-100 mt-3">Thanh toán ngay</button>
    </form>
</div>
</body>
</html>
