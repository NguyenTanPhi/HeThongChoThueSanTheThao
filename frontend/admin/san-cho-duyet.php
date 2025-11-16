<?php
require_once '../config.php';
requireRole('admin');
$user = getUser();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Duyệt sân mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px 0; }
        .card { border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); overflow: hidden; }
        .card-header { background: linear-gradient(45deg, #1e3799, #4a69bd); color: white; text-align: center; padding: 25px; }
        .card-header h1 { margin: 0; font-weight: 700; font-size: 2rem; }
        .stat-card { border-radius: 15px; padding: 20px; text-align: center; color: white; font-weight: bold; }
        .pending { background: linear-gradient(45deg, #f093fb, #f5576c); }
        .approved { background: linear-gradient(45deg, #4facfe, #00f2fe); }
        .stat-number { font-size: 3rem; font-weight: 800; }
        .table img { border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .btn-approve { background: #00d25b; border: none; }
        .btn-approve:hover { background: #00b894; }
        .btn-reject { background: #ff3e4d; border: none; }
        .btn-reject:hover { background: #e63341; }
        .no-data { text-align: center; padding: 60px; background: rgba(255,255,255,0.9); border-radius: 20px; margin: 30px 0; }
        .no-data i { font-size: 5rem; color: #95a5a6; margin-bottom: 20px; }
        .badge-status { font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h1>QUẢN TRỊ VIÊN - DUYỆT SÂN MỚI</h1>
            <p class="mb-0">Xin chào <strong><?= htmlspecialchars($user['name']) ?></strong>! Chúc bạn một ngày làm việc hiệu quả!</p>
        </div>
        
        <div class="card-body p-4">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="stat-card pending">
                        <div class="stat-number" id="pending-count">0</div>
                        <div>Sân chờ duyệt</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card approved">
                        <div class="stat-number" id="approved-count">0</div>
                        <div>Tổng sân đã duyệt</div>
                    </div>
                </div>
            </div>

            <?php
            $response = callAPI('GET', '/admin/san/cho-duyet', null, $_SESSION['token']);
            $sanList = $response ?? [];

            if (!empty($sanList)) {
                echo '<div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>STT</th>
                                <th>Ảnh sân</th>
                                <th>Tên sân</th>
                                <th>Chủ sân</th>
                                <th>Loại sân</th>
                                <th>Giá/giờ</th>
                                <th>Địa chỉ</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>';

                foreach ($sanList as $index => $san) {
                    $ownerName = $san['owner']['name'] ?? 'Không rõ';
                    $ownerEmail = $san['owner']['email'] ?? '';
                    $hinhAnh = !empty($san['hinh_anh']) 
                        ? "http://127.0.0.1:8000/storage/" . $san['hinh_anh'] 
                        : "https://via.placeholder.com/100x80/cccccc/666666?text=No+Image";
                    
                   

                    echo "<tr data-san-id='{$san['id']}'>
                        <td><strong>" . ($index + 1) . "</strong></td>
                        <td><img src='$hinhAnh' width='100' height='80' alt='Ảnh sân'></td>
                        <td><strong>" . htmlspecialchars($san['ten_san']) . "</strong></td>
                        <td>
                            <div><strong>$ownerName</strong></div>
                            <small class='text-muted'>$ownerEmail</small>
                        </td>
                        <td><span class='badge bg-info'>" . htmlspecialchars($san['loai_san']) . "</span></td>
                        <td><strong class='text-success'>" . number_format($san['gia_thue']) . "đ</strong></td>
                        <td>" . htmlspecialchars($san['dia_chi']) . "</td>
                        <td class='status-cell'>
                            <span class='badge bg-warning'>Chờ duyệt</span>
                        </td>
                        <td>
                            <button type='button' class='btn btn-approve btn-sm btn-duyet' data-id='{$san['id']}'>
                                DUYỆT
                            </button>
                            <button type='button' class='btn btn-reject btn-sm btn-tuchoi' data-id='{$san['id']}'>
                                TỪ CHỐI
                            </button>
                        </td>
                    </tr>";
                }
                echo '</tbody></table></div>';
            } else {
                echo '<div class="no-data">
                    <i class="fas fa-check-circle"></i>
                    <h3>Không có sân nào đang chờ duyệt</h3>
                    <p>Hệ thống đang rất ổn định! Bạn có thể nghỉ ngơi một chút.</p>
                </div>';
            }
            ?>
            
            <div class="text-center mt-4">
                <a href="dashboard.php" class="btn btn-light btn-lg">
                    Quay lại Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('pending-count').textContent = '<?= count($sanList) ?>';

fetch('http://127.0.0.1:8000/api/san?trang_thai_duyet=da_duyet', {
    headers: { 'Authorization': 'Bearer <?= $_SESSION['token'] ?>' }
})
.then(r => r.json())
.then(data => {
    const count = Array.isArray(data) ? data.length : (data.data ? data.data.length : 0);
    document.getElementById('approved-count').textContent = count;
});

// === XỬ LÝ DUYỆT & TỪ CHỐI BẰNG AJAX + SWEETALERT ===
const API_URL = 'http://127.0.0.1:8000/api';
const token = '<?= $_SESSION['token'] ?>';

document.addEventListener('DOMContentLoaded', function() {
    // DUYỆT SÂN
    document.querySelectorAll('.btn-duyet').forEach(btn => {
        btn.addEventListener('click', function() {
            const sanId = this.dataset.id;
            const row = this.closest('tr');

            Swal.fire({
                title: 'Duyệt sân?',
                text: 'Sân sẽ được hiển thị công khai!',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Duyệt ngay',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#00d25b'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch(`${API_URL}/admin/san/${sanId}/duyet`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ trang_thai_duyet: 'da_duyet' })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.message && data.message.includes('thành công')) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công!',
                                text: 'Đã duyệt sân thành công!',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            row.querySelector('.status-cell').innerHTML = '<span class="badge bg-success">Đã duyệt</span>';
                            row.querySelectorAll('button').forEach(b => b.disabled = true);
                            updatePendingCount();
                        } else {
                            throw new Error(data.message || 'Lỗi server');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Lỗi!', err.message, 'error');
                    });
                }
            });
        });
    });

    // TỪ CHỐI SÂN
    document.querySelectorAll('.btn-tuchoi').forEach(btn => {
        btn.addEventListener('click', function() {
            const sanId = this.dataset.id;
            const row = this.closest('tr');

            Swal.fire({
                title: 'Từ chối sân?',
                html: '<textarea id="lydo" class="swal2-textarea" placeholder="Nhập lý do từ chối..."></textarea>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Từ chối',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#ff3e4d',
                preConfirm: () => {
                    const lydo = document.getElementById('lydo').value.trim();
                    if (!lydo) {
                        Swal.showValidationMessage('Vui lòng nhập lý do');
                        return false;
                    }
                    return lydo;
                }
            }).then(result => {
                if (result.isConfirmed) {
                    fetch(`${API_URL}/admin/san/${sanId}/duyet`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            trang_thai_duyet: 'tu_choi',
                            ly_do: result.value
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.message && data.message.includes('thành công')) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công!',
                                text: 'Đã từ chối sân!',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            row.querySelector('.status-cell').innerHTML = '<span class="badge bg-danger">Từ chối</span>';
                            row.querySelectorAll('button').forEach(b => b.disabled = true);
                            updatePendingCount();
                        } else {
                            throw new Error(data.message || 'Lỗi server');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Lỗi!', err.message, 'error');
                    });
                }
            });
        });
    });

    // Cập nhật số lượng chờ duyệt
    function updatePendingCount() {
        const remaining = document.querySelectorAll('tr[data-san-id] .badge.bg-warning').length;
        document.getElementById('pending-count').textContent = remaining;
    }
});
</script>
</body>
</html>