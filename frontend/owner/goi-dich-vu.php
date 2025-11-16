<?php
require_once '../config.php';
requireRole('owner');
$user = getUser();

// LẤY DANH SÁCH GÓI DỊCH VỤ
$response = callAPI('GET', '/goi-dich-vu', null, $_SESSION['token']);
$dsGoi = is_array($response) ? $response : (isset($response->data) ? $response->data : []);

// LẤY GÓI HIỆN TẠI
$goiHienTai = callAPI('GET', '/owner/goi-hien-tai', null, $_SESSION['token']);
$goiHienTai = is_array($goiHienTai) ? $goiHienTai : null;

$now = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nâng cấp gói - <?= htmlspecialchars($user['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root { --primary: #fd7e14; --success: #28a745; --warning: #ffc107; }
        body { background: linear-gradient(135deg, #ff9a3d 0%, #fd7e14 100%); min-height: 100vh; font-family: 'Segoe UI', sans-serif; color: white; }
        .card-goi { border: none; border-radius: 25px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.4); transition: all 0.4s ease; background: white; }
        .card-goi:hover { transform: translateY(-25px); box-shadow: 0 35px 70px rgba(253,126,20,0.6); }
        .card-header-goi { padding: 50px 20px; background: linear-gradient(45deg, #fd7e14, #e67e22); color: white; text-align: center; }
        .badge-current { position: absolute; top: 15px; right: -20px; background: #28a745; color: white; padding: 15px 40px; font-weight: bold; font-size: 1.1rem; transform: rotate(12deg); box-shadow: 0 5px 15px rgba(0,0,0,0.4); z-index: 10; }
        .btn-mua { border-radius: 50px; padding: 20px 60px; font-size: 1.4rem; font-weight: bold; background: #ffc107; color: #212529; border: none; transition: all 0.3s; }
        .btn-mua:hover { background: #ffca2c; transform: scale(1.05); }
        .feature-icon { font-size: 1.8rem; margin-right: 15px; width: 50px; text-align: center; }
        .text-orange { color: #fd7e14; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow-lg" style="background: linear-gradient(45deg, #fd7e14, #e67e22);">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold fs-2" href="quan-ly-san.php">
            <i class="fas fa-futbol me-2"></i> Chủ Sân: <?= htmlspecialchars($user['name']) ?>
        </a>
        <a href="quan-ly-san.php" class="btn btn-outline-light btn-lg px-4">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
</nav>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-2 fw-bold text-white mb-3">
            <i class="fas fa-rocket text-warning"></i> Nâng cấp gói dịch vụ
        </h1>
        <p class="lead fs-3 text-white opacity-90">Đăng không giới hạn sân trong thời gian gói</p>
    </div>

    <!-- Gói hiện tại -->
    <?php if ($goiHienTai): ?>
        <div class="alert alert-success text-center fs-4 shadow-lg border-0">
            <i class="fas fa-star text-warning"></i>
            <strong class="ms-2">Gói hiện tại:</strong> <?= htmlspecialchars($goiHienTai['ten_goi'] ?? 'N/A') ?> 
            - Hết hạn: <strong><?= isset($goiHienTai['ngay_het_han']) ? date('d/m/Y', strtotime($goiHienTai['ngay_het_han'])) : 'Không xác định' ?></strong>
        </div>
    <?php endif; ?>

    <!-- Danh sách gói -->
    <?php if (empty($dsGoi)): ?>
        <div class="text-center py-5">
            <i class="fas fa-box-open fa-6x text-white mb-4 opacity-40"></i>
            <h2 class="text-white">Chưa có gói dịch vụ nào</h2>
            <p class="text-white opacity-75 fs-5">Vui lòng liên hệ quản trị viên</p>
        </div>
    <?php else: ?>
        <div class="row g-5 justify-content-center">
            <?php foreach ($dsGoi as $goi): 
                if (!is_array($goi)) continue;
                $ten = $goi['ten_goi'] ?? 'Gói không tên';
                $gia = (int)($goi['gia'] ?? 0);
                $thoiHan = (int)($goi['thoi_han'] ?? 30);
                $moTa = $goi['mo_ta'] ?? 'Không có mô tả';
                $trangThai = $goi['trang_thai'] ?? 'hoat_dong';
                $id = $goi['id'] ?? 0;
                $dangDung = $goiHienTai && ($goiHienTai['ten_goi'] ?? '') === $ten;
            ?>
                <?php if ($trangThai !== 'hoat_dong') continue; ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card card-goi h-100 position-relative text-dark">
                        <?php if ($dangDung): ?>
                            <div class="badge-current">
                                <i class="fas fa-check-circle"></i> ĐANG DÙNG
                            </div>
                        <?php endif; ?>
                        <div class="card-header-goi">
                            <h2 class="mb-0 fw-bold"><?= htmlspecialchars($ten) ?></h2>
                            <div class="fs-1 fw-bold mt-3">₫<?= number_format($gia) ?></div>
                            <small class="fs-4 opacity-90">/ <?= $thoiHan ?> ngày</small>
                        </div>
                        <div class="card-body text-center p-5">
                            <p class="text-muted mb-4 fs-5"><?= htmlspecialchars($moTa) ?></p>
                            <ul class="list-unstyled text-start fs-5">
                                <li class="mb-4 text-success">
                                    <i class="fas fa-infinity feature-icon text-warning"></i>
                                    <strong class="text-orange">KHÔNG GIỚI HẠN</strong> số sân đăng<br>
                                    <small class="text-muted ms-5">Trong suốt <?= $thoiHan ?> ngày</small>
                                </li>
                                <li class="mb-4 text-success">
                                    <i class="fas fa-bolt feature-icon text-danger"></i>
                                    Duyệt sân siêu nhanh: <strong>15-30 phút</strong>
                                </li>
                                <li class="mb-4 text-success">
                                    <i class="fas fa-headset feature-icon text-primary"></i>
                                    Hỗ trợ ưu tiên 24/7 qua Zalo
                                </li>
                                <li class="mb-4 text-success">
                                    <i class="fas fa-crown feature-icon text-warning"></i>
                                    <?= $gia >= 900000 ? 'TOP 1 + Banner homepage' : 'Hiển thị nổi bật' ?>
                                </li>
                            </ul>
                            <button onclick="muaGoi(<?= $id ?>, '<?= htmlspecialchars(addslashes($ten)) ?>', <?= $gia ?>)"
                                    class="btn btn-mua w-100 shadow-lg mt-4">
                                <i class="fas fa-shopping-cart"></i>
                                <?= $dangDung ? 'GIA HẠN NGAY' : 'MUA NGAY' ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function muaGoi(id, ten, gia) {
    Swal.fire({
        title: 'Xác nhận mua gói',
        html: `
            <div class="text-start">
                <h4><strong>${ten}</strong></h4>
                <p class="mb-2"><i class="fas fa-coins text-warning"></i> Giá: <strong class="text-danger">${new Intl.NumberFormat('vi-VN').format(gia)}₫</strong></p>
                <p class="mb-2"><i class="fas fa-infinity text-success"></i> Không giới hạn sân</p>
                <p><i class="fas fa-calendar text-primary"></i> Hiệu lực: <strong>30 ngày</strong></p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-qrcode"></i> Thanh toán MoMo',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#fd7e14'
    }).then((result) => {
        if (result.isConfirmed) {
            window.currentGoiId = id; // ✅ Lưu ID gói đang chọn

            Swal.fire({ 
                title: 'Đang tạo hóa đơn...', 
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            
            fetch('http://127.0.0.1:8000/api/owner/mua-goi', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer <?= $_SESSION['token'] ?>'
                },
                body: JSON.stringify({ goi_dich_vu_id: id })
            })
            .then(r => r.json())
            .then(data => {
                if (data.payUrl) {
                    Swal.fire({
                        title: 'Quét MoMo để thanh toán',
                        html: `<img src="${data.payUrl}" class="img-fluid" style="max-width: 280px;">`,
                        confirmButtonText: 'Đã thanh toán',
                        allowOutsideClick: false
                    }).then(() => checkThanhToan(data.orderId));
                } else {
                    Swal.fire('Lỗi!', data.message || 'Không tạo được hóa đơn!', 'error');
                }
            })
            .catch(() => Swal.fire('Lỗi!', 'Không kết nối được server!', 'error'));
        }
    });
}

function checkThanhToan(orderId) {
    const goiId = window.currentGoiId; // ✅ Lấy ID gói để gửi backend

    const interval = setInterval(() => {
        fetch('http://127.0.0.1:8000/api/owner/check-thanh-toan/' + orderId, {
            method: 'POST', // ✅ Bắt buộc POST
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer <?= $_SESSION['token'] ?>'
            },
            body: JSON.stringify({ goi_dich_vu_id: goiId })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                clearInterval(interval);
                Swal.fire({
                    title: 'Thành công!',
                    text: 'Gói đã được kích hoạt! Bạn có thể đăng không giới hạn sân trong 30 ngày!',
                    icon: 'success',
                    confirmButtonText: 'Tuyệt vời!'
                }).then(() => location.reload());
            }
        });
    }, 3000);

    // Sau 5 phút thì dừng kiểm tra
    setTimeout(() => {
        clearInterval(interval);
        Swal.fire('Hết thời gian', 'Thanh toán chưa hoàn tất. Vui lòng thử lại!', 'warning');
    }, 300000);
}
</script>
</body>
</html>
