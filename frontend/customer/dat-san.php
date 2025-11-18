<?php
require_once '../config.php';

// Chỉ customer mới vào được
requireRole('customer');

$user = getUser();

// Lấy thông tin từ GET
$san_id = $_GET['san_id'] ?? 0;
$ngay = $_GET['ngay'] ?? '';
$gio_bat_dau = $_GET['gio_bat_dau'] ?? '';
$gio_ket_thuc = $_GET['gio_ket_thuc'] ?? '';
$giaKhungGio = $_GET['gia'] ?? 0;

if (!$san_id || !$ngay || !$gio_bat_dau || !$gio_ket_thuc) {
    die('<div class="alert alert-danger">Thiếu thông tin khung giờ để đặt sân!</div>');
}

// Gọi API lấy chi tiết sân
$san = callAPI('GET', '/san/' . $san_id, null, $_SESSION['token']);
if (!$san || !is_array($san)) {
    die('<div class="alert alert-danger">Không tìm thấy sân!</div>');
}


// Khi submit, chuyển sang trang thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Location: thanh-toan.php?san_id=$san_id&ngay=$ngay&gio_bat_dau=$gio_bat_dau&gio_ket_thuc=$gio_ket_thuc&gia=$giaKhungGio");
    exit;
}

// Ảnh sân
$imgUrl = 'https://via.placeholder.com/800x400?text=Chưa+có+ảnh';
if (!empty($san['hinh_anh'])) {
    $imgPath = $san['hinh_anh'];
    if (strpos($imgPath, 'storage/') === false && strpos($imgPath, 'public/') === false) {
        $imgPath = 'storage/' . $imgPath;
    }
    $imgPath = str_replace('public/', 'storage/', $imgPath);
    $imgUrl = 'http://127.0.0.1:8000/' . ltrim($imgPath, '/');
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt sân - <?= htmlspecialchars($san['ten_san']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f6f8; }
        .container { margin-top: 50px; max-width: 700px; }
        .card { border-radius: 12px; padding: 25px; background-color: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .img-sand { width: 100%; height: 280px; object-fit: cover; border-radius: 12px; }
        .info-label { font-weight: bold; }
        .btn-back { margin-top: 20px; }
        .form-control[disabled] { background-color: #e9ecef; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <img src="<?= $imgUrl ?>" class="img-sand" alt="<?= htmlspecialchars($san['ten_san']) ?>">

        <h2 class="mt-3"><?= htmlspecialchars($san['ten_san']) ?></h2>
        <p><span class="info-label">Loại sân:</span> <?= htmlspecialchars($san['loai_san'] ?? '-') ?></p>
        <p><span class="info-label">Địa chỉ:</span> <?= htmlspecialchars($san['dia_chi'] ?? '-') ?></p>

        <form method="post" class="mt-4">
            <div class="mb-3">
                <label class="info-label">Ngày:</label>
                <input type="text" class="form-control" value="<?= date('d/m/Y', strtotime($ngay)) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="info-label">Giờ:</label>
                <input type="text" class="form-control" value="<?= substr($gio_bat_dau,0,5) ?> - <?= substr($gio_ket_thuc,0,5) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="info-label">Giá khung giờ:</label>
                <input type="text" class="form-control" value="<?= number_format($giaKhungGio) ?>₫" disabled>
            </div>
            <button type="submit" class="btn btn-success w-100">Thanh toán & Xác nhận</button>
        </form>

        <a href="chi-tiet-san.php?id=<?= $san_id ?>" class="btn btn-secondary btn-back w-100 mt-3">Quay lại</a>
    </div>
</div>
</body>
</html>
