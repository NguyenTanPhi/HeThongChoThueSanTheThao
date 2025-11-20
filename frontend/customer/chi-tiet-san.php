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

// Lấy lịch trống
$lichRaw = callAPI('GET', "/customer/san/{$id}/lich-trong", null, $_SESSION['token']);
$lichTrong = [];

if (isset($lichRaw['data'])) {
    $data = $lichRaw['data'];
    if (is_object($data)) $data = json_decode(json_encode($data), true);
    if (is_array($data)) {
        foreach ($data as $l) {
            $l = is_object($l) ? (array)$l : $l;
            if (!empty($l['ngay']) && !empty($l['gio_bat_dau']) && !empty($l['gio_ket_thuc'])) {
                $lichTrong[] = $l;
            }
        }
    }
}

// Hàm xử lý ảnh
function getSanImage($hinh_anh) {
    if (empty($hinh_anh)) {
        return 'https://via.placeholder.com/1200x600/2c3e50/ffffff?text=SAN+BONG+DEP';
    }
    $path = $hinh_anh;
    if (strpos($path, 'storage/') === false && strpos($path, 'public/') === false) {
        $path = 'storage/' . ltrim($path, '/');
    }
    $path = str_replace('public/', 'storage/', $path);
    return 'http://127.0.0.1:8000/' . ltrim($path, '/');
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
        }
        nav a:hover { color: #3498db; }

        .san-hero {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
            margin: 2rem 0;
        }
        .san-img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            transition: transform 0.5s;
        }
        .san-img:hover {
            transform: scale(1.05);
        }
        .san-info {
            padding: 2.5rem;
        }
        .price-tag {
            font-size: 2rem;
            font-weight: bold;
            color: #28a745;
        }
        .slot-card {
            background: white;
            border-radius: 16px;
            padding: 1.2rem;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: all 0.3s;
            height: 100%;
        }
        .slot-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(40, 167, 69, 0.25);
        }
        .slot-date {
            font-size: 1.1rem;
            font-weight: bold;
            color: #2c3e50;
        }
        .slot-time {
            font-size: 1.3rem;
            font-weight: bold;
            color: #3498db;
            margin: 10px 0;
        }
        .slot-price {
            font-size: 1.2rem;
            color: #28a745;
            font-weight: bold;
        }
        .btn-book {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: bold;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s;
        }
        .btn-book:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.4);
        }
        .no-slot {
            background: white;
            border-radius: 16px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header>
    <div class="container">
        <h1 class="text-center mb-0">Chi tiết sân bóng</h1>
    </div>
</header>

<!-- MENU -->
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
        
        <div class="san-info">
            <h2 class="mb-3"><?= htmlspecialchars($san['ten_san']) ?></h2>
            
            <div class="row">
                <div class="col-md-6">
                    <p><i class="bi bi-shield-check text-success"></i> <strong>Loại sân:</strong> <?= htmlspecialchars($san['loai_san'] ?? 'Không xác định') ?></p>
                    <p><i class="bi bi-geo-alt-fill text-primary"></i> <strong>Địa chỉ:</strong> <?= htmlspecialchars($san['dia_chi'] ?? '-') ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="price-tag mb-0">
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

    <!-- LỊCH TRỐNG -->
    <h3 class="mb-4 text-center">
        Lịch trống hiện có
    </h3>

    <?php if (!empty($lichTrong)): ?>
        <div class="row g-4">
            <?php foreach ($lichTrong as $lich): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="slot-card">
                        <div class="slot-date">
                            <?= date('d/m', strtotime($lich['ngay'])) ?>
                            <small class="d-block text-muted"><?= date('l', strtotime($lich['ngay'])) ?></small>
                        </div>
                        <div class="slot-time">
                            <?= substr($lich['gio_bat_dau'], 0, 5) ?> - <?= substr($lich['gio_ket_thuc'], 0, 5) ?>
                        </div>
                        <div class="slot-price">
                            <?= number_format($lich['gia'] ?? $san['gia_thue']) ?>₫
                        </div>
                        <a href="dat-san.php?san_id=<?= $san['id'] ?>
                            &ngay=<?= $lich['ngay'] ?>
                            &gio_bat_dau=<?= $lich['gio_bat_dau'] ?>
                            &gio_ket_thuc=<?= $lich['gio_ket_thuc'] ?>
                            &gia=<?= $lich['gia'] ?? $san['gia_thue'] ?>"
                           class="btn btn-success btn-book text-white">
                            Đặt ngay
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-slot">
            <h4 class="text-muted">Hiện tại chưa có khung giờ trống</h4>
            <p>Vui lòng quay lại sau hoặc liên hệ chủ sân</p>
            <a href="home.php" class="btn btn-outline-primary mt-3">Quay lại trang chủ</a>
        </div>
    <?php endif; ?>

    <div class="text-center mt-5">
        <a href="home.php<?= isset($_GET['ten_san']) ? '?ten_san=' . urlencode($_GET['ten_san']) : '' ?>" 
           class="btn btn-lg btn-secondary">
            Quay lại danh sách sân
        </a>
    </div>
</div class="my-5"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>