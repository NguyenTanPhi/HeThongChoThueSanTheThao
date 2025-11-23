    <?php
    require_once '../config.php';
    requireRole('owner');

    $user = getUser();

    // LẤY DỮ LIỆU TỪ API
    $mySan = callAPI('GET', '/owner/my-san', null, $_SESSION['token']) ?? [];
    $goiDichVu = callAPI('GET', '/owner/goi-dich-vu', null, $_SESSION['token']) ?? [];
    $lichSuDat = callAPI('GET', '/owner/lich-su-dat?trang_thai=da_thanh_toan', null, $_SESSION['token']) ?? [];
    $lichTrong = callAPI('GET', '/owner/lich-trong', null, $_SESSION['token']) ?? [];

    // CHECKMARK ĐÃ THÊM: LẤY GÓI HIỆN TẠI TỪ BẢNG `goidamua`
    $goiHienTai = callAPI('GET', '/owner/goi-hien-tai', null, $_SESSION['token']) ?? null;

    // Đếm sân đã duyệt (PHP 7.3)
    $daDuyetCount = 0;
    if (is_array($mySan)) {
        foreach ($mySan as $s) {
            if (isset($s['trang_thai_duyet']) && $s['trang_thai_duyet'] === 'da_duyet') {
                $daDuyetCount++;
            }
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Quản lý Sân - <?= htmlspecialchars($user['name']) ?></title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
        <style>
            :root {
                --primary: #fd7e14;
                --primary-dark: #e67e22;
                --success: #28a745;
                --danger: #dc3545;
                --warning: #ffc107;
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
            .card-custom {
                border: none;
                border-radius: 20px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.15);
                transition: all 0.3s ease;
            }
            .card-custom:hover {
                transform: translateY(-10px);
                box-shadow: 0 20px 40px rgba(253,126,20,0.3);
            }
            .btn-vip {
                background: linear-gradient(45deg, #ffc107, #ff8f00);
                color: #212529;
                font-weight: bold;
                border: none;
                border-radius: 50px;
                padding: 12px 30px;
            }
            .status-badge {
                padding: 8px 16px;
                border-radius: 50px;
                font-size: 0.85rem;
                font-weight: bold;
            }
            .pending { background: #fff3cd; color: #856404; }
            .approved { background: #d4edda; color: #155724; }
            .active { background: #d1ecf1; color: #0c5460; }
            .expired { background: #f8d7da; color: #721c24; }
            .rejected { background: #f8d7da; color: #721c24; }
        </style>
    </head>
    <body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-owner sticky-top shadow-sm">
        <div class="container-fluid">
            <!-- Logo + Tên chủ sân -->
            <a class="navbar-brand d-flex align-items-center fw-bold fs-4" href="#">
                <i class="fas fa-futbol me-2 text-warning"></i>
                Chủ Sân: <?= htmlspecialchars($user['name']) ?>
            </a>

            <!-- Nút collapse cho mobile -->
            <button class="navbar-toggler border-warning" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" 
                    aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Nội dung collapse -->
            <div class="collapse navbar-collapse" id="navbarContent">
                <div class="ms-auto d-flex align-items-center gap-2">

                    <!-- Nút Thêm sân -->
                    <a href="them-san.php" class="btn btn-light btn-sm d-flex align-items-center gap-1 shadow-sm">
                        <i class="fas fa-plus"></i> Thêm sân
                    </a>
                    <a href="thong-tin.php" class="btn btn-light btn-sm d-flex align-items-center gap-1 shadow-sm">
                        <i class=""></i> Thông tin cá nhân
                    </a>
                    <!-- Chuông thông báo -->
                    <div class="position-relative">
                        <button class="btn btn-link text-white position-relative p-2 rounded-circle hover-bg-light" 
                                id="notif-bell" style="font-size:1.3rem; transition: all 0.2s;">
                            <i class="fas fa-bell"></i>
                            <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle" 
                                id="notif-badge" style="font-size:0.65rem; display:none; min-width:18px; height:18px; line-height:12px;">
                                0
                            </span>
                        </button>
                    </div>

                    <!-- Nút Đăng xuất -->
                    <a href="../logout.php" class="btn btn-outline-light btn-sm d-flex align-items-center gap-1 shadow-sm">
                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row g-4">

            <!-- CHECKMARK PHẦN GÓI DỊCH VỤ – ĐÃ SỬA HOÀN HẢO -->
            <div class="col-lg-4">
                <div class="card card-custom h-100 text-center">
                    <div class="card-body p-4">
                        <h4>Gói dịch vụ hiện tại</h4>
                        <?php if ($goiHienTai && !empty($goiHienTai['ten_goi'])): ?>
                            <h3 class="text-primary"><?= htmlspecialchars($goiHienTai['ten_goi']) ?></h3>
                            <p class="mb-2">Còn lại: <strong><?= htmlspecialchars($goiHienTai['ngay_con_lai'] ?? '0') ?> ngày</strong></p>
                            <p class="text-muted small">Hết hạn: <?= date('d/m/Y', strtotime($goiHienTai['ngay_het_han'] ?? now())) ?></p>
                        <?php else: ?>
                            <p class="text-danger fw-bold">Chưa có gói dịch vụ</p>
                        <?php endif; ?>
                        <a href="goi-dich-vu.php" class="btn btn-vip mt-3">
                            NÂNG CẤP / GIA HẠN
                        </a>
                    </div>
                </div>
            </div>

            <!-- DOANH THU -->
            <div class="col-lg-4">
                <div class="card card-custom h-100 bg-success text-white">
                    <div class="card-body text-center p-4">
                        <h4>Doanh thu hôm nay</h4>
                        <h2 class="display-5">₫<?= number_format($goiDichVu['doanh_thu_hom_nay'] ?? 0) ?></h2>
                        <a href="thong-ke.php" class="btn btn-vip mt-3">
    <i class="fas fa-chart-line"></i> Thống kê
</a>

                    </div>
                </div>
            </div>

            <!-- TỔNG SÂN -->
            <div class="col-lg-4">
                <div class="card card-custom h-100 bg-info text-white">
                    <div class="card-body text-center p-4">
                        <h4>Tổng số sân</h4>
                        <h2 class="display-5"><?= count($mySan) ?></h2>
                        <p><?= $daDuyetCount ?> đã duyệt</p>
                    </div>
                </div>
            </div>
            <!-- DANH SÁCH SÂN CỦA CHỦ SÂN -->
    <div class="col-12 mt-4">
        <div class="card card-custom">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Danh sách sân của bạn (<?= count($mySan) ?>)</h4>
            </div>
            <div class="card-body">
                <?php if (is_array($mySan) && count($mySan) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên sân</th>
                                    <th>Loại sân</th>
                                    <th>Giá thuê</th>
                                    <th>Địa chỉ</th>
                                    <th>Trạng thái</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mySan as $san): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($san['ten_san'] ?? '') ?></strong></td>
                                        <td><?= htmlspecialchars($san['loai_san'] ?? '-') ?></td>
                                        <td>₫<?= number_format($san['gia_thue'] ?? 0) ?></td>
                                        <td><?= htmlspecialchars($san['dia_chi'] ?? '-') ?></td>
                                        <td>
                                            <?php
                                                $status = $san['trang_thai_duyet'] ?? 'cho_duyet';
                                                $color = $status === 'da_duyet' ? 'success' : ($status === 'tu_choi' ? 'danger' : 'warning');
                                                $text = $status === 'da_duyet' ? 'Đã duyệt' : ($status === 'tu_choi' ? 'Từ chối' : 'Chờ duyệt');
                                            ?>
                                            <span class="badge bg-<?= $color ?>"><?= $text ?></span>
                                        </td>
                                        <td>
                                            <a href="chi-tiet-san.php?id=<?= $san['id'] ?>" class="btn btn-info btn-sm">
                                                Xem chi tiết
                                            </a>
                                            <a href="sua-san.php?id=<?= $san['id'] ?>" class="btn btn-warning btn-sm">
            <i class="fas fa-edit"></i> Sửa
        </a>
        <button onclick="xoaSan(<?= $san['id'] ?>)" class="btn btn-danger btn-sm">
            <i class="fas fa-trash"></i> Xóa
        </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-futbol fa-3x mb-3"></i>
                        <p>Bạn chưa thêm sân nào!</p>
                        <a href="them-san.php" class="btn btn-success">+ Thêm sân mới</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


            <!-- DANH SÁCH SÂN + YÊU CẦU THUÊ SÂN – GIỮ NGUYÊN NHƯ BẠN -->
            <!-- (Mình giữ nguyên phần bạn đã fix rất tốt) -->
            <!-- ... (phần danh sách sân và yêu cầu thuê sân giữ nguyên 100%) ... -->

            <!-- DANH SÁCH CÁC KHUNG GIỜ ĐÃ ĐƯỢC ĐẶT -->
    <div clas<div class="col-12 mt-4">
        <div class="card card-custom">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Danh sách đơn được thuê</h4>
            </div>
            <div class="card-body">
                <?php 
                    // Lọc dữ liệu chỉ lấy các đặt sân ĐÃ THANH TOÁN
                    $lichSuDatDaThanhToan = [];
                    if (is_array($lichSuDat)) {
                        foreach ($lichSuDat as $dat) {
                            // Kiểm tra trường 'trang_thai' trong dữ liệu trả về từ API
                            if (isset($dat['trang_thai']) && $dat['trang_thai'] === 'da_thanh_toan') {
                                $lichSuDatDaThanhToan[] = $dat;
                            }
                        }
                    }
                ?>
                <?php if (count($lichSuDatDaThanhToan) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Khách hàng</th>
                                    <th>Sân</th>
                                    <th>Ngày</th>
                                    <th>Giờ</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lichSuDatDaThanhToan as $ls): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($ls['user']['name'] ?? '---') ?></strong></td>
                                        <td><?= htmlspecialchars($ls['san']['ten_san'] ?? '---') ?></td>
                                        
                                        <td><?= date('d/m/Y', strtotime($ls['ngay_dat'] ?? '1970-01-01')) ?></td>
                                        
                                        <td>
                                            <?= htmlspecialchars($ls['gio_bat_dau'] ?? '--:--') ?> - 
                                            <?= htmlspecialchars($ls['gio_ket_thuc'] ?? '--:--') ?>
                                        </td>
                                        
                                        <td class="text-success fw-bold">
                                            ₫<?= number_format($ls['tong_gia'] ?? 0) ?>
                                        </td>
                                        
                                        <td>
                                            <span class="badge bg-success">Đã thanh toán</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-calendar-check fa-3x mb-2"></i><br>
                        Chưa có lịch đặt sân nào đã được thanh toán!
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    async function xoaSan(id) {
        const result = await Swal.fire({
            title: 'Xác nhận xóa?',
            text: 'Sân sẽ bị xóa vĩnh viễn!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#dc3545'
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch('http://127.0.0.1:8000/api/san/' + id, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer <?= $_SESSION['token'] ?>',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Đã xóa!',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                location.reload();
            } else {
                throw new Error(data.message || 'Xóa thất bại');
            }
        } catch (error) {
            Swal.fire('Lỗi!', error.message || 'Không thể kết nối server!', 'error');
        }
    }

    </script>
    <script>
    // === THÔNG BÁO CHO CHỦ SÂN ===
    const API_URL = 'http://127.0.0.1:8000/api';
    const token = '<?= $_SESSION['token'] ?>';

    function loadNotifications() {
        fetch(`${API_URL}/owner/notifications`, {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('notif-badge');
            const list = document.getElementById('notif-list');
            const timeEl = document.getElementById('notif-time');
            const count = data.unread_count || 0;

            // Cập nhật badge
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }

            // Cập nhật thời gian
            timeEl.textContent = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });

            // Render danh sách
            if (!data.notifications || data.notifications.length === 0) {
                list.innerHTML = `
                    <div class="p-3 text-center text-muted small">
                        <i class="fas fa-bell-slash fa-2x mb-2"></i><br>
                        Không có thông báo mới
                    </div>`;
                return;
            }

            list.innerHTML = data.notifications.map(n => `
                <a href="#" class="list-group-item list-group-item-action ${!n.da_doc ? 'bg-light border-start border-primary border-3' : ''}"
                onclick="markAsRead(${n.id}); return false;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1 me-2">
                            <div class="small fw-bold ${!n.da_doc ? 'text-primary' : ''}">${n.noi_dung}</div>
                            ${n.ly_do ? `<div class="text-danger small mt-1"><strong>Nội dung:</strong> ${n.ly_do}</div>` : ''}
                        </div>
                        <small class="text-muted text-nowrap">${formatDate(n.created_at)}</small>
                    </div>
                </a>
            `).join('');
        })
        .catch(err => {
            console.error('Lỗi load thông báo:', err);
            document.getElementById('notif-list').innerHTML = `
                <div class="p-3 text-center text-danger small">
                    <i class="fas fa-exclamation-triangle"></i><br>
                    Không thể tải thông báo
                </div>`;
        });
    }

    function formatDate(date) {
        const d = new Date(date);
        const now = new Date();
        const diff = now - d;
        const day = d.getDate().toString().padStart(2, '0');
        const month = (d.getMonth() + 1).toString().padStart(2, '0');
        const hour = d.getHours().toString().padStart(2, '0');
        const minute = d.getMinutes().toString().padStart(2, '0');

        if (diff < 86400000) { // < 24h
            return `Hôm nay, ${hour}:${minute}`;
        } else if (diff < 172800000) {
            return `Hôm qua, ${hour}:${minute}`;
        } else {
            return `${day}/${month}, ${hour}:${minute}`;
        }
    }

    function markAsRead(id) {
        fetch(`${API_URL}/owner/notifications/${id}/read`, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            }
        })
        .then(() => loadNotifications())
        .catch(() => {});
    }

    // === TOGGLE DROPDOWN ===
    document.getElementById('notif-bell').addEventListener('click', function(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('notif-dropdown');
        const isVisible = dropdown.style.display === 'block';
        dropdown.style.display = isVisible ? 'none' : 'block';
        if (!isVisible) loadNotifications();
    });

    // Đóng khi click ngoài
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('notif-dropdown');
        if (!e.target.closest('#notif-bell') && dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
        }
    });

    // Tự động cập nhật mỗi 30 giây
    setInterval(loadNotifications, 30000);

    // Load lần đầu
    document.addEventListener('DOMContentLoaded', loadNotifications);
    </script>
    <!-- DROPDOWN THÔNG BÁO -->
    <div id="notif-dropdown" class="dropdown-menu dropdown-menu-end p-0 shadow-lg border-0" 
        style="display:none; width:380px; max-height:500px; overflow:hidden; position:absolute; top:60px; right:10px; z-index:9999;">
        <div class="p-3 border-bottom bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Thông báo</h6>
            <small id="notif-time" class="text-light"></small>
        </div>
        <div id="notif-list" class="list-group list-group-flush" style="max-height:400px; overflow-y:auto;">
            <!-- JS sẽ load -->
        </div>
        <div class="p-2 text-center border-top bg-light">
            <a href="notifications.php" class="text-decoration-none small text-muted">Xem tất cả →</a>
        </div>
    </div>
    </body>
    </html>