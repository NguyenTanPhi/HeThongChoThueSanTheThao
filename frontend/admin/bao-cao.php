<?php
require_once '../config.php';
requireRole('admin');
$user = getUser();

// Lọc ngày
$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';

$params = [];
if ($from) $params['from'] = $from;
if ($to)   $params['to']   = $to;

// Gọi API
$bookingRevenue = callAPI('GET', '/admin/bao-cao/dat-san', $params, $_SESSION['token']) ?? [];
$packageRevenue = callAPI('GET', '/admin/bao-cao/goi-dich-vu', $params, $_SESSION['token']) ?? [];

// Tính tổng riêng biệt
$doanhThuChuSan   = array_sum(array_column($bookingRevenue, 'so_tien'));   // Chủ sân nhận
$doanhThuHeThong  = array_sum(array_column($packageRevenue, 'gia'));       // Admin nhận

// Thống kê tổng quan (fallback an toàn)
$totalSan = $totalOwner = $totalCustomer = 0;
try {
    $sanList = callAPI('GET', '/admin/san', ['trang_thai_duyet' => 'da_duyet'], $_SESSION['token']);
    $totalSan = is_array($sanList) ? count($sanList) : 0;

    $ownerList = callAPI('GET', '/admin/nguoi-dung', ['role' => 'owner'], $_SESSION['token']);
    $totalOwner = is_array($ownerList) ? count($ownerList) : 0;

    $customerList = callAPI('GET', '/admin/nguoi-dung', ['role' => 'customer'], $_SESSION['token']);
    $totalCustomer = is_array($customerList) ? count($customerList) : 0;
} catch (Exception $e) { /* ignore */ }
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo doanh thu hệ thống</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fc; padding: 30px; font-family: 'Segoe UI', sans-serif; }
        .card-custom { border-radius: 15px; box-shadow: 0 6px 25px rgba(0,0,0,0.1); overflow: hidden; }
        .revenue-admin { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .revenue-owner { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; }
        .highlight-box { padding: 30px; border-radius: 15px; text-align: center; font-weight: bold; }
        .filter-box { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .stat-card { background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .stat-number { font-size: 2.5rem; font-weight: bold; color: #1e3799; }
        .total-row { background: #e3f2fd !important; font-weight: bold; font-size: 1.1rem; }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="dashboard.php" class="btn btn-secondary">Quay lại Dashboard</a>
    </div>
    <div>
        <a href="export-excel.php?<?= http_build_query($_GET) ?>" class="btn btn-success me-2">
            Xuất Excel (CSV)
        </a>
        <a href="export-pdf.php?<?= http_build_query($_GET) ?>" class="btn btn-danger">
            In PDF
        </a>
    </div>
</div>

    <!-- Bộ lọc ngày -->
    <div class="filter-box mb-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-bold">Từ ngày</label>
                <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">
            </div>
            <div class="col-md-5">
                <label class="form-label fw-bold">Đến ngày</label>
                <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Lọc</button>
            </div>
        </form>
        <?php if ($from || $to): ?>
            <div class="mt-3 text-success fw-bold">
                Đang xem: <?= $from ? date('d/m/Y', strtotime($from)) : 'đầu tiên' ?> 
                → <?= $to ? date('d/m/Y', strtotime($to)) : 'hôm nay' ?>
                <a href="?" class="ms-2 text-danger small">Xóa lọc</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row mb-5 g-4">
        <div class="col-md-3">
            <div class="stat-card border-start border-primary border-5">
                <div class="stat-number"><?= number_format($totalSan) ?></div>
                <div class="stat-label">Tổng số sân</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-start border-success border-5">
                <div class="stat-number"><?= number_format($totalOwner) ?></div>
                <div class="stat-label">Chủ sân</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-start border-info border-5">
                <div class="stat-number"><?= number_format($totalCustomer) ?></div>
                <div class="stat-label">Khách hàng</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-start border-warning border-5">
                <div class="stat-number"><?= number_format($totalSan + $totalOwner + $totalCustomer) ?></div>
                <div class="stat-label">Tổng người dùng</div>
            </div>
        </div>
    </div>

    <!-- DOANH THU HỆ THỐNG (Gói dịch vụ) -->
    <div class="card card-custom mb-5 revenue-admin">
        <div class="card-body text-center highlight-box">
            <h3 class="mb-3">DOANH THU HỆ THỐNG<br><small>(Tiền từ bán gói dịch vụ - thuộc về Admin)</small></h3>
            <h1 class="display-3 mb-2"><?= number_format($doanhThuHeThong) ?> ₫</h1>
            <p class="fs-5">Từ <?= count($packageRevenue) ?> giao dịch mua gói</p>
        </div>
    </div>

    <!-- DOANH THU CHỦ SÂN (Đặt sân) -->
    <div class="card card-custom mb-5 revenue-owner">
        <div class="card-body text-center highlight-box">
            <h3 class="mb-3">DOANH THU CHỦ SÂN<br><small>(Tiền từ đặt sân - thuộc về chủ sân)</small></h3>
            <h1 class="display-3 mb-2"><?= number_format($doanhThuChuSan) ?> ₫</h1>
            <p class="fs-5">Từ <?= count($bookingRevenue) ?> lượt đặt sân thành công</p>
        </div>
    </div>

    <!-- CHI TIẾT ĐẶT SÂN (Chủ sân nhận) -->
    <div class="card card-custom mb-4">
        <div class="card-header bg-primary text-white">
            <h5>Chi tiết đặt sân – Doanh thu chủ sân nhận (<?= count($bookingRevenue) ?> lượt)</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($bookingRevenue)): ?>
                <p class="text-center py-5 text-muted">Không có lượt đặt sân nào trong khoảng thời gian này.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Tên sân</th>
                                <th>Người đặt</th>
                                <th>Ngày</th>
                                <th>Giờ chơi</th>
                                <th class="text-end">Số tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookingRevenue as $item): ?>
                            <tr>
                                <td><?= $item['dat_san_id'] ?></td>
                                <td><?= htmlspecialchars($item['ten_san']) ?></td>
                                <td><?= htmlspecialchars($item['nguoi_dat']) ?></td>
                                <td><?= date('d/m/Y', strtotime($item['ngay_dat'])) ?></td>
                                <td><?= substr($item['gio_bat_dau'],0,5) ?> - <?= substr($item['gio_ket_thuc'],0,5) ?></td>
                                <td class="text-end fw-bold text-danger"><?= number_format($item['so_tien']) ?>₫</td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="total-row table-primary">
                                <td colspan="5" class="text-end fw-bold">TỔNG DOANH THU CHỦ SÂN:</td>
                                <td class="text-end fw-bold text-danger fs-5"><?= number_format($doanhThuChuSan) ?>₫</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- CHI TIẾT GÓI DỊCH VỤ (Hệ thống nhận) -->
    <div class="card card-custom">
        <div class="card-header bg-success text-white">
            <h5>Chi tiết gói dịch vụ – Doanh thu hệ thống nhận (<?= count($packageRevenue) ?> giao dịch)</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($packageRevenue)): ?>
                <p class="text-center py-5 text-muted">Không có giao dịch mua gói nào.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Tên gói</th>
                                <th>Người mua</th>
                                <th>Ngày mua</th>
                                <th>Hết hạn</th>
                                <th class="text-end">Giá</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($packageRevenue as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($p['ten_goi']) ?></span></td>
                                <td><?= htmlspecialchars($p['nguoi_dung']) ?></td>
                                <td><?= date('d/m/Y', strtotime($p['ngay_mua'])) ?></td>
                                <td><?= date('d/m/Y', strtotime($p['ngay_het'])) ?></td>
                                <td class="text-end fw-bold text-danger"><?= number_format($p['gia']) ?>₫</td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="total-row table-success">
                                <td colspan="5" class="text-end fw-bold">TỔNG DOANH THU HỆ THỐNG:</td>
                                <td class="text-end fw-bold text-danger fs-5"><?= number_format($doanhThuHeThong) ?>₫</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>