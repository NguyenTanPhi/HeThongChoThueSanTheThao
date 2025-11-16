<?php
require_once '../config.php';
requireRole('owner');

$sanId = $_GET['san_id'] ?? 0;
if (!$sanId) die('Thiếu ID sân!');

$san = callAPI('GET', '/san/' . $sanId, null, $_SESSION['token']);
if (!$san) die('Không tìm thấy sân!');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm lịch trống - <?= htmlspecialchars($san['ten_san']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Thêm lịch trống cho: <?= htmlspecialchars($san['ten_san']) ?></h4>
                </div>
                <div class="card-body">
                    <form id="form-lich-trong">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Ngày <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="ngay" min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label>Giờ bắt đầu <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" name="gio_bat_dau" min="07:00" max="21:00" required>
                            </div>
                            <div class="col-md-3">
                                <label>Giờ kết thúc <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" name="gio_ket_thuc" min="08:00" max="22:00" required>
                            </div>
                            <!-- Trong file: them-lich-trong.php -->
<div class="col-md-6">
    <label>Giá thuê<small class="text-muted">(không nhập = dùng giá sân)</small></label>
    <input type="number" class="form-control" name="gia" min="0" placeholder="Ví dụ: 350000">
</div>
                        </div>
                        <button type="submit" class="btn btn-success mt-3">Thêm lịch</button>
                        <a href="chi-tiet-san.php?id=<?= $sanId ?>" class="btn btn-secondary mt-3">Quay lại</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('form-lich-trong').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('san_id', <?= $sanId ?>);
    formData.append('gia', this.querySelector('[name="gia"]').value || '');
    
    try {
        const res = await fetch(`http://127.0.0.1:8000/api/owner/san/<?= $sanId ?>/lich-trong`, {
            method: 'POST',
            headers: { 'Authorization': 'Bearer <?= $_SESSION['token'] ?>' },
            body: new URLSearchParams(formData)
        });
        
        const data = await res.json();
        
        if (data.success) {
            Swal.fire('Thành công!', data.message, 'success').then(() => {
                location.reload();
            });
        } else if (data.require_package) {
            Swal.fire('Cần gói dịch vụ!', data.message, 'warning');
        } else {
            Swal.fire('Lỗi!', data.message, 'error');
        }
    } catch (err) {
        Swal.fire('Lỗi!', 'Không kết nối được server!', 'error');
    }
});
</script>
</body>
</html>