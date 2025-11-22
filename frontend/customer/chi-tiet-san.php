<?php
require_once '../config.php';
requireRole('customer');
$user = getUser();

$id = $_GET['id'] ?? 0;
if (!$id) {
    die('<div class="alert alert-danger text-center p-5">Thiếu ID sân!</div>');
}

// Lấy chi tiết sân
$san = callAPI('GET', '/san/' . $id, null, $_SESSION['token']);
if (!$san || !is_array($san)) {
    die('<div class="alert alert-danger text-center p-5">Không tìm thấy sân bóng này!</div>');
}

// === LẤY ĐÁNH GIÁ ===
$danhGiaRes = callAPI('GET', '/danh-gia/san/' . $id, null, $_SESSION['token']);
$danhGiaList = $danhGiaRes['danh_gia'] ?? [];
$trungBinh = round($danhGiaRes['trung_binh'] ?? 0, 1);
$tongSo = $danhGiaRes['tong_so'] ?? 0;

// Tính % từng sao
$starCount = [5=>0,4=>0,3=>0,2=>0,1=>0];
foreach ($danhGiaList as $dg) {
    $starCount[$dg['diem_danh_gia'] ?? 5]++;
}
$totalStars = max($tongSo, 1);
$percent = [];
for ($i=5; $i>=1; $i--) {
    $percent[$i] = $tongSo > 0 ? round(($starCount[$i] / $totalStars) * 100) : 0;
}

// Lấy lịch trống
$lichRaw = callAPI('GET', "/customer/san/{$id}/lich-trong", null, $_SESSION['token']);
$lichTrong = [];
if (isset($lichRaw['data']) && is_array($lichRaw['data'])) {
    foreach ($lichRaw['data'] as $l) {
        $l = is_object($l) ? (array)$l : $l;
        if (!empty($l['ngay']) && !empty($l['gio_bat_dau']) && !empty($l['gio_ket_thuc'])) {
            $lichTrong[] = $l;
        }
    }
}

function getSanImage($hinh_anh) {
    if (empty($hinh_anh)) return 'https://via.placeholder.com/1200x600/2c3e50/ffffff?text=SAN+BONG+DEP';
    $path = $hinh_anh;
    if (strpos($path, 'storage/') === false) $path = 'storage/' . ltrim($path, '/');
    return 'http://127.0.0.1:8000/' . ltrim($path, '/');
}

function renderStars($rating) {
    $full = floor($rating);
    $half = ($rating - $full >= 0.3) ? 1 : 0;
    $empty = 5 - $full - $half;
    return str_repeat('<i class="bi bi-star-fill text-warning"></i>', $full) .
           ($half ? '<i class="bi bi-star-half text-warning"></i>' : '') .
           str_repeat('<i class="bi bi-star text-muted"></i>', $empty);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($san['ten_san']) ?> - Chi tiết sân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {font-family:'Segoe UI',sans-serif;background:linear-gradient(135deg,#f5f7fa,#c3cfe2);min-height:100vh;margin:0}
        header {background:linear-gradient(135deg,#2c3e50,#3498db);color:white;padding:2.5rem 0;box-shadow:0 4px 20px rgba(0,0,0,0.2)}
        nav {background-color:#ecf0f1;padding:15px 0;text-align:center;box-shadow:0 2px 10px rgba(0,0,0,0.1)}
        nav a {margin:0 30px;font-weight:600;color:#2c3e50;text-decoration:none;font-size:1.1rem}
        nav a:hover {color:#3498db}
        .san-hero {background:white;border-radius:20px;overflow:hidden;box-shadow:0 15px 40px rgba(0,0,0,.15);margin:2rem 0}
        .san-img {width:100%;height:400px;object-fit:cover;transition:transform .5s}
        .san-img:hover {transform:scale(1.05)}
        .rating-big {font-size:4.5rem;font-weight:bold;color:#f39c12}
        .star-bar {height:10px;background:#eee;border-radius:5px;overflow:hidden}
        .star-fill {height:100%;background:linear-gradient(90deg,#f39c12,#e67e22)}
        .review-card {background:white;border-radius:15px;padding:1.5rem;margin-bottom:1rem;box-shadow:0 5px 15px rgba(0,0,0,.08)}
        .avatar {width:50px;height:50px;border-radius:50%;object-fit:cover}
        .slot-card {background:white;border-radius:16px;padding:1.2rem;text-align:center;box-shadow:0 8px 25px rgba(0,0,0,.1);transition:all .3s;height:100%}
        .slot-card:hover {transform:translateY(-10px);box-shadow:0 20px 40px rgba(40,167,69,.3)}
    </style>
</head>
<body>

<!-- GIỮ NGUYÊN HEADER + NAV NHƯ FILE CŨ CỦA BẠN -->
<header>
    <div class="container">
        <h1 class="text-center mb-0">Chi tiết sân bóng</h1>
    </div>
</header>

<nav>
    <a href="home.php">Trang chủ</a>
    <a href="lich-su-dat.php">Lịch sử đặt sân</a>
    <a href="thong-tin.php">Thông tin cá nhân</a>
    <a href="../logout.php">Đăng xuất</a>
</nav>

<div class="container">

    <!-- THÔNG TIN SÂN -->
    <div class="san-hero">
        <img src="<?= getSanImage($san['hinh_anh'] ?? '') ?>" class="san-img" alt="<?= htmlspecialchars($san['ten_san']) ?>">
        
        <div class="san-info p-4 p-md-5">
            <h2 class="mb-3"><?= htmlspecialchars($san['ten_san']) ?></h2>
            
            <div class="row">
                <div class="col-md-6">
                    <p><i class="bi bi-shield-check text-success"></i> <strong>Loại sân:</strong> <?= htmlspecialchars($san['loai_san'] ?? 'Không xác định') ?></p>
                    <p><i class="bi bi-geo-alt-fill text-primary"></i> <strong>Địa chỉ:</strong> <?= htmlspecialchars($san['dia_chi'] ?? '-') ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="price-tag mb-0 text-success fs-3 fw-bold">
                        <?= number_format($san['gia_thue'] ?? 0) ?>₫
                        <small class="text-muted">/giờ</small>
                    </p>
                </div>
            </div>

            <?php if (!empty($san['mo_ta'])): ?>
                <hr>
                <h5>Mô tả chi tiết</h5>
                <p class="text-muted"><?= nl2br(htmlspecialchars($san['mo_ta'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- PHẦN ĐÁNH GIÁ SIÊU ĐẸP -->
    <div class="row my-5">
        <div class="col-lg-4">
            <div class="text-center p-4 bg-white rounded-4 shadow">
                <div class="rating-big"><?= $trungBinh ?></div>
                <div class="fs-3 mb-2"><?= renderStars($trungBinh) ?></div>
                <p class="text-muted fs-5"><?= $tongSo ?> đánh giá</p>
            </div>

            <div class="bg-white p-4 rounded-4 shadow mt-4">
                <?php for($i=5;$i>=1;$i--): ?>
                    <div class="d-flex align-items-center mb-2">
                        <span class="me-2 text-warning fw-bold"><?= $i ?> stars</span>
                        <div class="flex-grow-1 mx-2">
                            <div class="star-bar">
                                <div class="star-fill" style="width:<?= $percent[$i] ?>%"></div>
                            </div>
                        </div>
                        <span class="text-muted small"><?= $starCount[$i] ?></span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <div class="col-lg-8">
            <h3>Đánh giá từ khách hàng</h3>
            <?php if (empty($danhGiaList)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-chat-square-text" style="font-size:4rem"></i>
                    <p class="mt-3 fs-5">Chưa có đánh giá nào</p>
                </div>
            <?php else: ?>
                <?php foreach ($danhGiaList as $dg): ?>
                    <div class="review-card">
                        <div class="d-flex align-items-start">
                            <img src="<?= !empty($dg['avatar']) ? getSanImage($dg['avatar']) : 'https://ui-avatars.com/api/?name=' . urlencode($dg['ten_nguoi_dung'] ?? 'User') . '&background=2c3e50&color=fff' ?>"
                                 class="avatar me-3" alt="avatar">
                            <div class="flex-grow-1">
                                <strong><?= htmlspecialchars($dg['ten_nguoi_dung'] ?? 'Khách hàng') ?></strong>
                                <div class="text-warning mb-1">
                                    <?= renderStars($dg['diem_danh_gia'] ?? 5) ?>
                                    <small class="text-muted ms-2">
                                        <?= date('d/m/Y', strtotime($dg['ngay_danh_gia'] ?? now())) ?>
                                    </small>
                                </div>
                                <p class="mb-0"><?= nl2br(htmlspecialchars($dg['noi_dung'] ?? '')) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- LỊCH TRỐNG (GIỮ NGUYÊN NHƯ CŨ) -->
    <h3 class="mb-4 text-center">Lịch trống hiện có</h3>
    <?php if (!empty($lichTrong)): ?>
        <div class="row g-4">
            <?php foreach ($lichTrong as $lich): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="slot-card">
                        <div class="slot-date fw-bold">
                            <?= date('d/m', strtotime($lich['ngay'])) ?>
                            <small class="d-block text-muted"><?= ['Chủ nhật','Thứ 2','Thứ 3','Thứ 4','Thứ 5','Thứ 6','Thứ 7'][date('w', strtotime($lich['ngay']))] ?></small>
                        </div>
                        <div class="slot-time text-primary fw-bold fs-4">
                            <?= substr($lich['gio_bat_dau'],0,5) ?> - <?= substr($lich['gio_ket_thuc'],0,5) ?>
                        </div>
                        <div class="slot-price text-success fw-bold fs-5">
                            <?= number_format($lich['gia'] ?? $san['gia_thue']) ?>₫
                        </div>
                        <a href="dat-san.php?san_id=<?= $san['id'] ?>&ngay=<?= $lich['ngay'] ?>&gio_bat_dau=<?= $lich['gio_bat_dau'] ?>&gio_ket_thuc=<?= $lich['gio_ket_thuc'] ?>&gia=<?= $lich['gia'] ?? $san['gia_thue'] ?>"
                           class="btn btn-success mt-3 w-100 fw-bold">Đặt ngay</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5 bg-white rounded-4 shadow">
            <h4 class="text-muted">Hiện chưa có khung giờ trống</h4>
            <a href="home.php" class="btn btn-outline-primary mt-3">Quay lại trang chủ</a>
        </div>
    <?php endif; ?>

    <div class="text-center my-5">
        <a href="home.php" class="btn btn-lg btn-secondary rounded-pill px-5">
            Quay lại danh sách sân
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>