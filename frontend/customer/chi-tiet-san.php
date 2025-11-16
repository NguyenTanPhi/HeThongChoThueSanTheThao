<?php
require_once '../config.php';

// Chỉ customer mới vào được
requireRole('customer');

$user = getUser();

$id = $_GET['id'] ?? 0;
if (!$id) {
    die('<div class="alert alert-danger">Thiếu ID sân!</div>');
}

// Gọi API lấy chi tiết sân
$san = callAPI('GET', '/san/' . $id, null, $_SESSION['token']);
if (!$san || !is_array($san)) {
    die('<div class="alert alert-danger">Không tìm thấy sân!</div>');
}

// Gọi API lấy lịch trống do chủ sân khai báo
$lichTrongRaw = callAPI('GET', '/customer/san/' . $id . '/lich-trong', null, $_SESSION['token']);
$lichTrong = [];
if (isset($lichTrongRaw['data']) && is_array($lichTrongRaw['data'])) {
    foreach ($lichTrongRaw['data'] as $item) {
        $itemArr = is_object($item) ? (array)$item : $item;
        if (!empty($itemArr['ngay']) && !empty($itemArr['gio_bat_dau']) && !empty($itemArr['gio_ket_thuc'])) {
            $lichTrong[] = $itemArr;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết sân - <?= htmlspecialchars($san['ten_san']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f6f8; }
        .container { margin-top: 50px; margin-bottom: 50px; }
        .card-detail { border-radius: 10px; padding: 20px; background-color: #fff; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
        .img-sand { width: 100%; height: 300px; object-fit: cover; border-radius: 10px; }
        .info-label { font-weight: bold; }
        .badge-lich { margin: 2px; font-size: 0.9rem; }
        .slot-card { transition: transform 0.2s; }
        .slot-card:hover { transform: scale(1.05); }
    </style>
</head>
<body>
<div class="container">
    <div class="card card-detail mb-4">
        <?php
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
        <img src="<?= $imgUrl ?>" class="img-sand" alt="<?= htmlspecialchars($san['ten_san']) ?>">

        <h2 class="mt-3"><?= htmlspecialchars($san['ten_san']) ?></h2>
        <p><span class="info-label">Loại sân:</span> <?= htmlspecialchars($san['loai_san'] ?? '-') ?></p>
        <p><span class="info-label">Giá thuê/giờ:</span> <?= number_format($san['gia_thue'] ?? 0) ?>₫</p>
        <p><span class="info-label">Địa chỉ:</span> <?= htmlspecialchars($san['dia_chi'] ?? '-') ?></p>
        <p><span class="info-label">Mô tả:</span></p>
        <div class="bg-light p-3 rounded"><?= nl2br(htmlspecialchars($san['mo_ta'] ?? 'Chưa có mô tả.')) ?></div>
    </div>

    <h4 class="mb-3">Lịch trống</h4>
    <?php if (!empty($lichTrong)): ?>
        <div class="row g-3">
            <?php foreach ($lichTrong as $lich): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card slot-card text-center shadow-sm p-2">
                        <div class="card-body p-2">
                            <h6 class="card-title mb-1"><?= date('d/m', strtotime($lich['ngay'])) ?></h6>
                            <p class="card-text mb-1"><?= substr($lich['gio_bat_dau'],0,5) ?> - <?= substr($lich['gio_ket_thuc'],0,5) ?></p>
                            <p class="card-text fw-bold mb-2"><?= number_format($lich['gia']) ?>₫</p>
                            <a href="dat-san.php?san_id=<?= $san['id'] ?>
    &ngay=<?= $lich['ngay'] ?>
    &gio_bat_dau=<?= $lich['gio_bat_dau'] ?>
    &gio_ket_thuc=<?= $lich['gio_ket_thuc'] ?>
    &gia=<?= $lich['gia'] ?>" 
    class="btn btn-sm btn-success mb-1">
    Đặt
</a>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <span class="badge bg-secondary">Chưa có khung giờ trống</span>
    <?php endif; ?>

    <div class="mt-4">
        <a href="home.php" class="btn btn-secondary">Quay lại</a>
    </div>
</div>
</body>
</html>
