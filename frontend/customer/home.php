<?php
require_once '../config.php';
// Chỉ customer mới vào được
requireRole('customer');
$user = getUser();

// Lấy danh sách sân
$response = callAPI('GET', '/san', null, $_SESSION['token']);
$sanList = $response['data'] ?? [];
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
        header { background-color: #2c3e50; color: white; padding: 20px; text-align: center; position: relative; }
        nav { background-color: #ecf0f1; padding: 10px; text-align: center; }
        nav a { margin: 0 15px; text-decoration: none; color: #2980b9; font-weight: bold; }
        h2 { text-align: center; margin-top: 30px; color: #34495e; }
        .san-card { background-color: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin: 20px auto; padding: 20px; max-width: 600px; }
        .san-card img { width: 100%; height: 250px; object-fit: cover; border-radius: 8px; }
        .san-card h3 { margin-top: 10px; color: #2c3e50; }
        .san-card p { margin: 5px 0; color: #7f8c8d; }
        .san-card a { display: inline-block; margin-top: 10px; text-decoration: none; color: #27ae60; font-weight: bold; }
        .badge-lich { margin: 2px; font-size: 0.9rem; }

        /* Header: Đẩy nội dung vào giữa, chuông ở góc phải */
        header .container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }

        /* Chuông thông báo: GÓC PHẢI TRÊN CÙNG */
        .notification-bell {
            cursor: pointer; font-size: 1.6rem; color: white; transition: transform 0.2s ease;
            padding: 8px; border-radius: 50%;
        }
        .notification-bell:hover { transform: scale(1.15); background-color: rgba(255, 255, 255, 0.1); }
        .notification-badge {
            position: absolute; top: 4px; right: 4px; background: #e74c3c; color: white;
            border-radius: 50%; width: 22px; height: 22px; font-size: 0.75rem; font-weight: bold;
            display: flex; align-items: center; justify-content: center; border: 2px solid #2c3e50;
        }

        /* Modal styles */
        .notification-item { padding: 15px; border-bottom: 1px solid #eee; transition: background 0.2s; cursor: pointer; }
        .notification-item:hover { background-color: #f8f9fa; }
        .notification-item.unread { background-color: #e3f2fd; font-weight: 500; }
        .notification-time { font-size: 0.8rem; color: #95a5a6; }
    </style>
</head>
<body>

<header class="position-relative">
    <div class="container position-relative">
        <div class="d-flex justify-content-between align-items-center py-3">
            <h1 class="mb-0 text-white">Xin chào, <?= htmlspecialchars($user['name']) ?> (Khách hàng)</h1>
        </div>
    </div>
</header>

<nav>
    <a href="lich-su-dat.php">Lịch sử đặt sân</a>
    <a href="thong-tin.php">Thông tin cá nhân</a>
    <a href="../logout.php">Đăng xuất</a>
</nav>

<h2>Danh sách sân bóng</h2>

<div class="container">
<?php foreach ($sanList as $san): ?>
    <?php
        $san = (array)$san;
        $imgUrl = 'https://via.placeholder.com/600x250?text=Chưa+có+ảnh';
        if (!empty($san['hinh_anh'])) {
            $imgPath = $san['hinh_anh'];
            if (strpos($imgPath,'storage/') === false && strpos($imgPath,'public/') === false) {
                $imgPath = 'storage/' . $imgPath;
            }
            $imgPath = str_replace('public/', 'storage/', $imgPath);
            $imgUrl = 'http://127.0.0.1:8000/' . ltrim($imgPath,'/');
        }
        $lichTrongRaw = callAPI('GET', "/customer/san/{$san['id']}/lich-trong", null, $_SESSION['token']);
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
    <div class="san-card">
        <img src="<?= $imgUrl ?>" alt="<?= htmlspecialchars($san['ten_san']) ?>">
        <h3><?= htmlspecialchars($san['ten_san']) ?></h3>
        <p>Giá: <?= number_format($san['gia_thue'] ?? 0) ?>₫/giờ</p>
        <p>Địa chỉ: <?= htmlspecialchars($san['dia_chi'] ?? '-') ?></p>
        <p>
            Lịch trống:<br>
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
        <a href="chi-tiet-san.php?id=<?= $san['id'] ?>">Xem chi tiết & Đặt sân</a>
    </div>
<?php endforeach; ?>
</div>
</script>
</body>
</html>