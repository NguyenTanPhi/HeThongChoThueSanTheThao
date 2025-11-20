<?php
require_once '../config.php';
requireRole('admin');
$user = getUser();

$rawResponse = callAPI('GET', '/admin/goi-dich-vu', null, $_SESSION['token']);

$dsGoi = [];

if (is_string($rawResponse)) {
    $decoded = json_decode($rawResponse, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $dsGoi = isset($decoded['data']) ? $decoded['data'] : (is_array($decoded) ? $decoded : []);
    }
} elseif (is_array($rawResponse)) {
    $dsGoi = $rawResponse;
} elseif (is_object($rawResponse) && isset($rawResponse->data)) {
    $dsGoi = (array)$rawResponse->data;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý gói dịch vụ - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; min-height: 100vh; }
        .navbar-admin { background: linear-gradient(45deg, #0d6efd, #0d47a1); }
        .card { border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); transition: 0.3s; }
        .card:hover { transform: translateY(-10px); box-shadow: 0 15px 35px rgba(13,110,253,0.2); }
        .status-badge { padding: 8px 20px; border-radius: 50px; font-weight: bold; font-size: 0.9rem; }
        .empty-state { text-align: center; padding: 80px 20px; color: #6c757d; }
        .empty-state i { font-size: 4rem; color: #ddd; margin-bottom: 20px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-admin sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold fs-4" href="#">Admin: <?= htmlspecialchars($user['name']) ?></a>
        <a href="dashboard.php" class="btn btn-light btn-lg">
                    Quay lại Dashboard
                </a>
        <a href="../logout.php" class="btn btn-outline-light">Đăng xuất</a>

    </div>
</nav>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold text-primary">Quản lý gói dịch vụ</h1>
        <button class="btn btn-success btn-lg shadow" data-bs-toggle="modal" data-bs-target="#modalThem">
            Thêm gói mới
        </button>
    </div>

    <?php if (empty($dsGoi)): ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>Chưa có gói dịch vụ nào</h3>
            <p>Hãy thêm gói đầu tiên để bắt đầu!</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($dsGoi as $goi): 
                if (!is_array($goi)) continue;
                $id = $goi['id'] ?? 0;
                $ten = $goi['ten_goi'] ?? 'Chưa đặt tên';
                $moTa = $goi['mo_ta'] ?? '';
                $gia = (int)($goi['gia'] ?? 0);
                $thoiHan = (int)($goi['thoi_han'] ?? 30);
                $trangThai = $goi['trang_thai'] ?? 'ngung_ban';
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title fw-bold"><?= htmlspecialchars($ten) ?></h5>
                                <span class="status-badge <?= $trangThai === 'hoat_dong' ? 'bg-success text-white' : 'bg-secondary text-white' ?>">
                                    <?= $trangThai === 'hoat_dong' ? 'Hoạt động' : 'Ngừng bán' ?>
                                </span>
                            </div>
                            <p class="text-muted small"><?= htmlspecialchars($moTa) ?: 'Không có mô tả' ?></p>
                            <hr>
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <p class="mb-1 text-muted small">Giá</p>
                                    <h5 class="text-success fw-bold">₫<?= number_format($gia) ?></h5>
                                </div>
                                <div class="col-6">
                                    <p class="mb-1 text-muted small">Thời hạn</p>
                                    <h5 class="fw-bold"><?= $thoiHan ?> ngày</h5>
                                </div>
                            </div>
                            <div class="text-end">
                                <button onclick="suaGoi(<?= $id ?>, <?= json_encode($goi) ?>)"
                                        class="btn btn-warning btn-sm">Sửa</button>
                                <button onclick="xoaGoi(<?= $id ?>, '<?= htmlspecialchars(addslashes($ten)) ?>')"
                                        class="btn btn-danger btn-sm">Xóa</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Thêm gói -->
<div class="modal fade" id="modalThem" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Thêm gói dịch vụ mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formThem">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tên gói</label>
                            <input type="text" name="ten_goi" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giá (VNĐ)</label>
                            <input type="number" name="gia" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Thời hạn (ngày)</label>
                            <input type="number" name="thoi_han" class="form-control" min="1" value="30" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trạng thái</label>
                            <select name="trang_thai" class="form-select">
                                <option value="hoat_dong">Hoạt động</option>
                                <option value="ngung_ban">Ngừng bán</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mô tả</label>
                            <textarea name="mo_ta" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Thêm gói</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('formThem').onsubmit = function(e) {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(this));

    Swal.fire({ title: 'Đang thêm...', didOpen: () => Swal.showLoading() });

    fetch('http://127.0.0.1:8000/api/admin/goi-dich-vu', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer <?= $_SESSION['token'] ?>'
        },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(d => {
        if (d.message) {
            Swal.fire('Thành công!', d.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Lỗi!', d.message || 'Không thêm được!', 'error');
        }
    });
};

function suaGoi(id, goi) {
    Swal.fire({
        title: 'Sửa gói: ' + goi.ten_goi,
        html: `
            <input id="ten_goi" class="swal2-input" value="${goi.ten_goi}">
            <input id="gia" class="swal2-input" type="number" value="${goi.gia}">
            <input id="thoi_han" class="swal2-input" type="number" value="${goi.thoi_han}">
            <textarea id="mo_ta" class="swal2-textarea">${goi.mo_ta || ''}</textarea>
            <select id="trang_thai" class="swal2-select">
                <option value="hoat_dong" ${goi.trang_thai === 'hoat_dong' ? 'selected' : ''}>Hoạt động</option>
                <option value="ngung_ban" ${goi.trang_thai === 'ngung_ban' ? 'selected' : ''}>Ngừng bán</option>
            </select>
        `,
        showCancelButton: true,
        confirmButtonText: 'Cập nhật',
        preConfirm: () => ({
            ten_goi: document.getElementById('ten_goi').value,
            gia: document.getElementById('gia').value,
            thoi_han: document.getElementById('thoi_han').value,
            mo_ta: document.getElementById('mo_ta').value,
            trang_thai: document.getElementById('trang_thai').value
        })
    }).then(result => {
        if (result.isConfirmed) {
            fetch(`http://127.0.0.1:8000/api/admin/goi-dich-vu/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer <?= $_SESSION['token'] ?>' },
                body: JSON.stringify(result.value)
            }).then(() => location.reload());
        }
    });
}

function xoaGoi(id, ten) {
    Swal.fire({
        title: 'Xóa gói "' + ten + '"?',
        text: "Không thể khôi phục!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xóa luôn!'
    }).then(r => {
        if (r.isConfirmed) {
            fetch(`http://127.0.0.1:8000/api/admin/goi-dich-vu/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer <?= $_SESSION['token'] ?>' }
            }).then(() => location.reload());
        }
    });
}
</script>
</body>
</html>