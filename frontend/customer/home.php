<?php
require_once '../config.php';
requireRole('customer');
$user = getUser();

// === THAM SỐ TÌM KIẾM ===
$search = trim($_GET['ten_san'] ?? '');
$quan   = $_GET['quan'] ?? '';
$sort   = $_GET['sort'] ?? '';

// Lấy danh sách sân
$response = callAPI('GET', '/san', null, $_SESSION['token']);
$sanList = $response['data'] ?? [];

// Danh sách quận
$dsQuan = ['Quận 1','Quận 3','Quận 5','Quận 7','Quận 9','Quận 10','Quận 11','Bình Thạnh','Gò Vấp','Tân Bình','Phú Nhuận','Thủ Đức','Bình Tân','Tân Phú'];

// === LỌC + GỌI LỊCH TRỐNG (GIỮ NGUYÊN LOGIC GỐC CỦA BẠN) ===
$ketqua = [];

foreach ($sanList as $item) {
    $san = is_object($item) ? (array)$item : $item;

    // Lọc tên sân
    if ($search !== '' && stripos($san['ten_san'] ?? '', $search) === false) continue;

    // Lọc quận
    if ($quan !== '' && stripos($san['dia_chi'] ?? '', $quan) === false) continue;

    // GỌI API LỊCH TRỐNG – GIỐNG HỆT FILE GỐC CỦA BẠN
    $lichRaw = callAPI('GET', "/customer/san/{$san['id']}/lich-trong", null, $_SESSION['token']);
    $lichTrong = [];

    if (isset($lichRaw['data'])) {
        $data = $lichRaw['data'];
        // Xử lý cả mảng và object
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

    $san['lich_trong'] = $lichTrong;
    $ketqua[] = $san;
}

// === SẮP XẾP – DÙNG HÀM THƯỜNG (KHÔNG DÙNG fn() ĐỂ TRÁNH LỖI PHP CŨ) ===
if ($sort === 'gia_thap') {
    usort($ketqua, function($a, $b) {
        return ($a['gia_thue'] ?? 0) <=> ($b['gia_thue'] ?? 0);
    });
} elseif ($sort === 'gia_cao') {
    usort($ketqua, function($a, $b) {
        return ($b['gia_thue'] ?? 0) <=> ($a['gia_thue'] ?? 0);
    });
} elseif ($sort === 'ten') {
    usort($ketqua, function($a, $b) {
        return strcmp($a['ten_san'] ?? '', $b['ten_san'] ?? '');
    });
}

// Hàm xử lý ảnh – giống hệt file gốc
function getImg($hinh_anh) {
    $imgUrl = 'https://via.placeholder.com/600x250?text=Chưa+có+ảnh';
    if (!empty($hinh_anh)) {
        $imgPath = $hinh_anh;
        if (strpos($imgPath,'storage/') === false && strpos($imgPath,'public/') === false) {
            $imgPath = 'storage/' . $imgPath;
        }
        $imgPath = str_replace('public/', 'storage/', $imgPath);
        $imgUrl = 'http://127.0.0.1:8000/' . ltrim($imgPath,'/');
    }
    return $imgUrl;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang chủ khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #f4f6f8; margin:0; padding:0; }
        header { background: linear-gradient(135deg, #2c3e50, #3498db); color: white; padding: 2.5rem 0; }
        nav { background-color: #ecf0f1; padding: 15px 0; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        nav a { margin: 0 25px; text-decoration: none; color: #2c3e50; font-weight: bold; font-size: 1.1rem; }
        nav a:hover { color: #3498db; }
        .search-box { background: white; border-radius: 15px; padding: 1.5rem; box-shadow: 0 8px 25px rgba(0,0,0,0.1); margin: 2rem 0; }
        h2 { text-align: center; margin: 30px 0; color: #2c3e50; }
        .san-card { background-color: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); margin: 20px auto; padding: 20px; max-width: 700px; transition: 0.3s; }
        .san-card:hover { transform: translateY(-8px); box-shadow: 0 12px 30px rgba(0,0,0,0.18); }
        .san-card img { width: 100%; height: 250px; object-fit: cover; border-radius: 12px; }
        .san-card h3 { margin: 15px 0 10px; color: #2c3e50; }
        .san-card p { margin: 8px 0; color: #7f8c8d; }
        .san-card a { display: inline-block; margin-top: 15px; padding: 10px 20px; background: #27ae60; color: white; border-radius: 8px; text-decoration: none; font-weight: bold; }
        .san-card a:hover { background: #219653; }
        .badge-lich { margin: 4px; font-size: 0.9rem; padding: 0.5em 0.8em; }
        .result-count { font-size: 1.3rem; font-weight: bold; color: #2c3e50; text-align: center; margin: 20px 0; }
    </style>
</head>
<body>

<header>
    <div class="container">
        <h1 class="text-center mb-0">Xin chào, <?= htmlspecialchars($user['name']) ?> (Khách hàng)</h1>
    </div>
</header>

<nav>
    <a href="lich-su-dat.php">Lịch sử đặt sân</a>
    <a href="thong-tin.php">Thông tin cá nhân</a>
    <a href="../logout.php">Đăng xuất</a>
</nav>

<div class="container">

    <!-- Ô TÌM KIẾM -->
    <div class="search-box">
        <form method="GET" class="row g-3">
            <div class="col-md-5">
                <input type="text" name="ten_san" class="form-control form-control-lg" placeholder="Tìm tên sân..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="quan" class="form-select form-select-lg">
                    <option value="">Tất cả quận</option>
                    <?php foreach ($dsQuan as $q): ?>
                        <option value="<?= $q ?>" <?= $quan===$q?'selected':'' ?>><?= $q ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="sort" class="form-select form-select-lg">
                    <option value="">Sắp xếp</option>
                    <option value="gia_thap" <?= $sort==='gia_thap'?'selected':'' ?>>Giá tăng dần</option>
                    <option value="gia_cao" <?= $sort==='gia_cao'?'selected':'' ?>>Giá giảm dần</option>
                    <option value="ten" <?= $sort==='ten'?'selected':'' ?>>Tên A → Z</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-lg w-100">Tìm kiếm</button>
            </div>
        </form>
    </div>

    <div class="result-count">
        Tìm thấy <strong><?= count($ketqua) ?></strong> sân
        <?php if (!empty($_GET)): ?>
             — <a href="home.php" style="color:#e74c3c; text-decoration:underline;">Xóa bộ lọc</a>
        <?php endif; ?>
    </div>

    <?php if (empty($ketqua)): ?>
        <div class="text-center py-5">
            <h3 class="text-muted">Không tìm thấy sân nào</h3>
            <a href="home.php" class="btn btn-success btn-lg">Xem tất cả sân</a>
        </div>
    <?php else: foreach ($ketqua as $san): ?>
        <?php
            $imgUrl = getImg($san['hinh_anh'] ?? '');
            $lichTrong = $san['lich_trong'] ?? [];
        ?>
        <div class="san-card">
            <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($san['ten_san']) ?>">
            <h3><?= htmlspecialchars($san['ten_san']) ?></h3>
            <p>Giá: <strong class="text-success"><?= number_format($san['gia_thue'] ?? 0) ?>₫/giờ</strong></p>
            <p>Địa chỉ: <?= htmlspecialchars($san['dia_chi'] ?? '-') ?></p>
            <p>
                <strong>Lịch trống:</strong><br>
                <?php if (!empty($lichTrong)): ?>
                    <?php foreach ($lichTrong as $lich): ?>
                        <span class="badge bg-success badge-lich">
                            <?= date('d/m', strtotime($lich['ngay'])) ?>
                            <?= substr($lich['gio_bat_dau'],0,5) ?>-<?= substr($lich['gio_ket_thuc'],0,5) ?>
                        </span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="badge bg-secondary">Chưa có khung giờ trống</span>
                <?php endif; ?>
            </p>
            <a href="chi-tiet-san.php?id=<?= $san['id'] ?>&<?= http_build_query($_GET) ?>">Xem chi tiết & Đặt sân</a>
        </div>
    <?php endforeach; endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>