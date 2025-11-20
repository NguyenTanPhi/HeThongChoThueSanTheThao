<?php
require_once '../config.php';
requireRole('customer');
$user = getUser();

// Lấy lịch sử đặt sân
$response = callAPI('GET', '/customer/dat-san', null, $_SESSION['token']);
$datSanList = [];

if (!empty($response['data'])) {
    foreach ($response['data'] as $item) {
        $datSanList[] = is_object($item) ? (array)$item : $item;
    }
}

// Sắp xếp mới nhất lên đầu (dùng function thường để tương thích PHP cũ)
usort($datSanList, function($a, $b) {
    $timeA = $a['created_at'] ?? $a['ngay_dat'] ?? '0000-00-00';
    $timeB = $b['created_at'] ?? $b['ngay_dat'] ?? '0000-00-00';
    return strtotime($timeB) - strtotime($timeA);
});

// Hàm xác định trạng thái thực tế
function getRealStatus($item) {
    $dbStatus = $item['trang_thai'] ?? '';
    if ($dbStatus === 'da_huy') return 'da_huy';
    if ($dbStatus === 'hoan_thanh') return 'hoan_thanh';

    $ngay   = $item['ngay_dat'] ?? $item['ngay'] ?? date('Y-m-d');
    $gioKT  = $item['gio_ket_thuc'] ?? '00:00:00';
    if (strtotime($ngay . ' ' . $gioKT) < time()) {
        return 'hoan_thanh';
    }
    return 'da_thanh_toan';
}

// Badge trạng thái
function statusBadge($status) {
    $badges = [
        'da_thanh_toan' => '<span class="badge bg-warning text-dark px-3 py-2">Đã thanh toán</span>',
        'hoan_thanh'    => '<span class="badge bg-success px-3 py-2">Hoàn thành</span>',
        'da_huy'        => '<span class="badge bg-danger px-3 py-2">Đã hủy</span>',
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary px-3 py-2">Không rõ</span>';
}

// Hàm lọc danh sách theo trạng thái (thay thế fn() bằng function thường)
function filterByStatus($list, $status) {
    $result = [];
    foreach ($list as $item) {
        if (getRealStatus($item) === $status) {
            $result[] = $item;
        }
    }
    return $result;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lịch sử đặt sân</title>
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
            color: #2f3542;
            text-decoration: none;
            font-size: 1.1rem;
            transition: color 0.3s;
        }
        nav a:hover { color: #3498db; }

        .booking-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
            transition: all 0.4s;
            height: 100%;
        }
        .booking-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 20px 45px rgba(0,0,0,0.2);
        }
        .card-header-custom {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 1.5rem;
        }
        .status-badge {
            font-size: 1rem;
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
        }
        .price-big {
            font-size: 2rem;
            font-weight: bold;
            color: #28a745;
        }
        .btn-review {
            background: linear-gradient(135deg, #ff9a9e, #fad0c4);
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: bold;
            color: white;
            transition: all 0.3s;
        }
        .btn-review:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255,154,158,0.4);
        }
        .empty-state {
            text-align: center;
            padding: 6rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .tab-custom .nav-link {
            border-radius: 12px 12px 0 0;
            padding: 1rem 2rem;
            font-weight: 600;
            color: #6c757d;
        }
        .tab-custom .nav-link.active {
            background: white;
            color: #2c3e50;
            border-bottom: 4px solid #3498db;
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header>
    <div class="container">
        <h1 class="text-center mb-0">Lịch sử đặt sân của bạn</h1>
    </div>
</header>

<!-- MENU -->
<nav>
    <a href="home.php">Trang chủ</a>
    <a href="thong-tin.php">Thông tin cá nhân</a>
    <a href="../logout.php">Đăng xuất</a>
</nav>

<div class="container my-5">

    <?php if (empty($datSanList)): ?>
        <div class="empty-state">
            <i class="bi bi-calendar-x display-1 text-muted mb-4"></i>
            <h3 class="text-muted">Bạn chưa đặt sân nào</h3>
            <p class="text-muted mb-4">Hãy tìm và đặt sân ngay hôm nay!</p>
            <a href="home.php" class="btn btn-success btn-lg px-5">
                Tìm sân bóng ngay
            </a>
        </div>

    <?php else: ?>

        <!-- Tabs trạng thái -->
        <ul class="nav nav-tabs tab-custom mb-5" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all">
                    Tất cả <span class="badge bg-secondary ms-1"><?= count($datSanList) ?></span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#dathang">Đã thanh toán</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#hoanthanh">Hoàn thành</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#dahuy">Đã hủy</button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="all">
                <?= renderBookings($datSanList) ?>
            </div>
            <div class="tab-pane fade" id="dathang">
                <?= renderBookings(filterByStatus($datSanList, 'da_thanh_toan')) ?>
            </div>
            <div class="tab-pane fade" id="hoanthanh">
                <?= renderBookings(filterByStatus($datSanList, 'hoan_thanh')) ?>
            </div>
            <div class="tab-pane fade" id="dahuy">
                <?= renderBookings(filterByStatus($datSanList, 'da_huy')) ?>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php
function renderBookings($list) {
    if (empty($list)) {
        return '<div class="text-center py-5"><h5 class="text-muted">Không có đơn nào ở trạng thái này</h5></div>';
    }

    $html = '<div class="row g-4">';
    foreach ($list as $d) {
        $san       = $d['san'] ?? ['ten_san' => 'Sân không xác định', 'dia_chi' => '', 'id' => 0];
        if (is_object($san)) $san = (array)$san;

        $sanId     = $san['id'] ?? 0;
        $datSanId  = $d['id'] ?? 0;
        $ngay      = $d['ngay_dat'] ?? $d['ngay'] ?? '2025-01-01';
        $gioBD     = substr($d['gio_bat_dau'] ?? '', 0, 5);
        $gioKT     = substr($d['gio_ket_thuc'] ?? '', 0, 5);
        $gia       = $d['tong_gia'] ?? $d['tong_tien'] ?? 0;
        $created   = $d['created_at'] ?? 'now';
        $status    = getRealStatus($d);

        $reviewBtn = ($status === 'hoan_thanh')
            ? '<a href="danh-gia.php?san_id='. $sanId .'&dat_san_id='. $datSanId .'" class="btn btn-review">
                 Đánh giá sân
               </a>'
            : '';

        $html .= '
        <div class="col-md-6 col-lg-4">
            <div class="booking-card">
                <div class="card-header-custom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">'. htmlspecialchars($san['ten_san']) .'</h5>
                        '. statusBadge($status) .'
                    </div>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-3">
                        '. htmlspecialchars($san['dia_chi'] ?? 'Không có địa chỉ') .'
                    </p>
                    <div class="mb-3">
                        <p class="mb-1"><strong>Ngày chơi:</strong> '. date('d/m/Y (l)', strtotime($ngay)) .'</p>
                        <p class="mb-1"><strong>Khung giờ:</strong> '. $gioBD .' → '. $gioKT .'</p>
                    </div>
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="price-big">'. number_format($gia) .'₫</div>
                            <small class="text-muted">Đặt lúc '. date('H:i, d/m', strtotime($created)) .'</small>
                        </div>
                        <div>
                            '. $reviewBtn .'
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }
    $html .= '</div>';
    return $html;
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>