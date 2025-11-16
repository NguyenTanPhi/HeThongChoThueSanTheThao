<?php
require_once '../config.php';
requireRole('owner');

$id = $_GET['id'] ?? 0;
if (!$id) {
    die('<div class="alert alert-danger">Thiếu ID sân!</div>');
}

$san = callAPI('GET', '/san/' . $id, null, $_SESSION['token']);
if (!$san || !is_array($san)) {
    die('<div class="alert alert-danger">Không tìm thấy sân!</div>');
}

$user = getUser();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết sân - <?= htmlspecialchars($san['ten_san']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #fd7e14;
            --primary-dark: #e67e22;
        }
        body {
            background: linear-gradient(135deg, #ff9a3d 0%, #fd7e14 100%);
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }
        .navbar-owner {
            background: linear-gradient(45deg, var(--primary), var(--primary-dark));
            box-shadow: 0 4px 20px rgba(253,126,20,0.4);
        }
        .card-detail {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .card-detail:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(253,126,20,0.3);
        }
        .img-sand {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-bottom: 5px solid var(--primary);
        }
        .info-label {
            font-weight: bold;
            color: var(--primary-dark);
        }
        .status-badge {
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        .pending { background: #fff3cd; color: #856404; }
        .approved { background: #d4edda; color: #155724; }
        .rejected { background: #f8d7da; color: #721c24; }
        @media (max-width: 768px) {
            .img-sand { height: 250px; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-owner sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold fs-4" href="quan-ly-san.php">
            Chủ Sân: <?= htmlspecialchars($user['name']) ?>
        </a>
        <div>
            <a href="quan-ly-san.php" class="btn btn-light me-2">
                Quản lý sân
            </a>
            <a href="them-lich-trong.php?san_id=<?= $san['id'] ?>" class="btn btn-primary me-2">
                <i class="fas fa-calendar-plus"></i> Quản lý lịch trống
            </a>
            <a href="../logout.php" class="btn btn-outline-light">
                Đăng xuất
            </a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card card-detail">
                <!-- ẢNH SÂN -->
                <?php
                $imgUrl = 'https://via.placeholder.com/800x400/cccccc/666666?text=Chưa+có+ảnh';
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

                <div class="card-body p-5">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <h2 class="mb-0"><?= htmlspecialchars($san['ten_san']) ?></h2>
                        <?php
                        $status = $san['trang_thai_duyet'] ?? 'cho_duyet';
                        $badgeClass = $status === 'da_duyet' ? 'approved' : ($status === 'tu_choi' ? 'rejected' : 'pending');
                        $badgeText = $status === 'da_duyet' ? 'Đã duyệt' : ($status === 'tu_choi' ? 'Từ chối' : 'Chờ duyệt');
                        ?>
                        <span class="status-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <p class="mb-2"><span class="info-label">Loại sân:</span> <?= htmlspecialchars($san['loai_san'] ?? '-') ?></p>
                            <p class="mb-2"><span class="info-label">Giá thuê/giờ:</span> 
                                <strong class="text-success fs-5">₫<?= number_format($san['gia_thue'] ?? 0) ?></strong>
                            </p>
                            <p class="mb-2"><span class="info-label">Địa chỉ:</span> <?= htmlspecialchars($san['dia_chi'] ?? '-') ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><span class="info-label">Mô tả:</span></p>
                            <div class="bg-light p-3 rounded">
                                <?= nl2br(htmlspecialchars($san['mo_ta'] ?? 'Chưa có mô tả.')) ?>
                            </div>
                        </div>
                    </div>

                    <!-- LỊCH TRỐNG -->
                    <div class="mt-5">
                        <h4 class="mb-3"><i class="fas fa-calendar-alt text-primary"></i> Lịch trống</h4>
                        <div id="lich-trong-container" class="table-responsive">
                            <div class="text-center py-3">
                                <i class="fas fa-spinner fa-spin"></i> Đang tải lịch...
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 text-center">
                        <a href="quan-ly-san.php" class="btn btn-secondary">
                            Quay lại
                        </a>
                        <a href="sua-san.php?id=<?= $san['id'] ?>" class="btn btn-warning ms-3">
                            Sửa sân
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL SỬA LỊCH -->
<div class="modal fade" id="modalSuaLich" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Sửa lịch trống</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-sua-lich">
                <div class="modal-body">
                    <input type="hidden" name="lich_id" id="sua-lich-id">
                    <input type="hidden" name="san_id" value="<?= $san['id'] ?>">

                    <div class="mb-3">
                        <label>Ngày</label>
                        <input type="date" class="form-control" name="ngay" id="sua-ngay" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Giờ bắt đầu</label>
                            <input type="time" class="form-control" name="gio_bat_dau" id="sua-gio-bat-dau" required>
                        </div>
                        <div class="col-md-6">
                            <label>Giờ kết thúc</label>
                            <input type="time" class="form-control" name="gio_ket_thuc" id="sua-gio-ket-thuc" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label>Giá thuê (₫)</label>
                        <input type="number" class="form-control" name="gia" id="sua-gia" min="0" placeholder="Không nhập = dùng giá sân">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const API_URL = 'http://127.0.0.1:8000/api'; // ← KHỞI TẠO BIẾN tại đây
    const sanId = <?= $san['id'] ?>;
    const modalSuaLich = new bootstrap.Modal(document.getElementById('modalSuaLich'));

    fetch(`http://127.0.0.1:8000/api/owner/san/${sanId}/lich-trong`, {
        headers: {
            'Authorization': 'Bearer <?= $_SESSION['token'] ?>',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw err; });
        }
        return response.json();
    })
    .then(data => {
        const container = document.getElementById('lich-trong-container');

        if (data.require_package) {
            container.innerHTML = `
                <div class="alert alert-warning text-center p-4">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3"></i><br>
                    <strong>${data.message}</strong>
                    <br><a href="goi-dich-vu.php" class="btn btn-warning mt-3">Mua gói ngay</a>
                </div>`;
            return;
        }

        if (!data || data.length === 0) {
            container.innerHTML = `
                <div class="alert alert-info text-center p-4">
                    <i class="fas fa-calendar-times fa-2x mb-3"></i><br>
                    Chưa có khung giờ trống nào
                </div>`;
            return;
        }

        let html = `
            <table class="table table-hover table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Ngày</th>
                        <th>Khung giờ</th>
                        <th>Giá</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>`;

        data.forEach(item => {
            const ngayVN = new Date(item.ngay).toLocaleDateString('vi-VN', {
                weekday: 'short', day: '2-digit', month: '2-digit'
            });
            const gio = `${item.gio_bat_dau.slice(0,5)} - ${item.gio_ket_thuc.slice(0,5)}`;
            const giaHienThi = item.gia ? Number(item.gia).toLocaleString('vi-VN') : 'Mặc định';
            
            html += `
                <tr>
                    <td><strong>${ngayVN}</strong></td>
                    <td>${gio}</td>
                    <td>₫${giaHienThi}</td>
                    <td>
                        <button class="btn btn-warning btn-sm me-1" onclick="suaLich(${item.id}, '${item.ngay}', '${item.gio_bat_dau}', '${item.gio_ket_thuc}', ${item.gia || 'null'})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="xoaLich(${item.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
        });

        html += `</tbody></table>`;
        container.innerHTML = html;
    })
    .catch(err => {
        console.error('Lỗi:', err);
        const msg = err.message || 'Không thể kết nối server';
        document.getElementById('lich-trong-container').innerHTML = 
            `<div class="alert alert-danger p-4"><i class="fas fa-bug"></i> Lỗi: ${msg}</div>`;
    });

    // HÀM SỬA LỊCH
    window.suaLich = function(lichId, ngay, gioBatDau, gioKetThuc, gia) {
        document.getElementById('sua-lich-id').value = lichId;
        document.getElementById('sua-ngay').value = ngay;
        document.getElementById('sua-gio-bat-dau').value = gioBatDau;
        document.getElementById('sua-gio-ket-thuc').value = gioKetThuc;
        document.getElementById('sua-gia').value = gia === null ? '' : gia;
        modalSuaLich.show();
    };

    // HÀM XÓA LỊCH
    window.xoaLich = function(lichId) {
        Swal.fire({
            title: 'Xóa khung giờ?',
            text: 'Khách hàng sẽ không đặt được!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#dc3545'
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`http://127.0.0.1:8000/api/owner/san/<?= $san['id'] ?>/lich-trong/${lichId}`, {
                    method: 'DELETE',
                    headers: { 
                        'Authorization': 'Bearer <?= $_SESSION['token'] ?>',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Đã xóa!', '', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Lỗi!', data.message || 'Không thể xóa', 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Lỗi!', 'Không kết nối được server!', 'error');
                });
            }
        });
    };

    // XỬ LÝ FORM SỬA
   document.getElementById('form-sua-lich').addEventListener('submit', async function(e) { 
    e.preventDefault();
    const formData = new FormData(this);
    const lichId = document.getElementById('sua-lich-id').value;

    // Chuyển giá trị rỗng thành null để Laravel hiểu là nullable
    let giaValue = formData.get('gia');
    if (!giaValue || isNaN(giaValue)) {
        formData.set('gia', ''); // hoặc xóa đi: formData.delete('gia');
    } else {
        formData.set('gia', parseFloat(giaValue));
    }

    // Chuyển giờ sang H:i nếu vô tình browser gửi HH:MM:SS
    let gioBD = formData.get('gio_bat_dau');
    let gioKT = formData.get('gio_ket_thuc');
    formData.set('gio_bat_dau', gioBD.slice(0,5));
    formData.set('gio_ket_thuc', gioKT.slice(0,5));

    let ngay = formData.get('ngay');
    formData.set('ngay', ngay); // giữ nguyên YYYY-MM-DD
    formData.set('_method', 'PUT'); // thêm field _method để Laravel hiểu PU
    try {
        
        const res = await fetch(`${API_URL}/owner/san/<?= $san['id'] ?>/lich-trong/${lichId}`, {
            method: 'POST', // Laravel dùng POST + _method=PUT
            headers: {  
                'Authorization': 'Bearer <?= $_SESSION['token'] ?>',
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await res.json();
        if (res.status === 422) {
            // Hiển thị lỗi validation từ Laravel
            let msg = Object.values(data.errors || {}).flat().join('<br>');
            Swal.fire('Lỗi Validation', msg, 'error');
            return;
        }

        if (data.success) {
            Swal.fire('Thành công!', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Lỗi!', data.message || 'Cập nhật thất bại', 'error');
        }
    } catch (err) {
        console.error('Lỗi:', err);
        Swal.fire('Lỗi!', `Không thể kết nối: ${err.message}`, 'error');
    }
});
});
</script>
</body>
</html>