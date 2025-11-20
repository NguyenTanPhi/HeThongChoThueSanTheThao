<?php
require_once '../config.php';
requireRole('owner');
$user = getUser();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống Kê - Chủ Sân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <h2 class="text-center mb-4">📊 Thống kê doanh thu</h2>

    <!-- Nút quay lại -->
    <div class="mb-3">
        <a href="quan-ly-san.php" class="btn btn-secondary">
            ⬅ Quay lại trang chủ sân    
        </a>
    </div>

    <!-- Form lọc -->
    <form id="filterForm" class="row g-3 bg-white p-4 shadow rounded">


    <!-- Form lọc -->
    <form id="filterForm" class="row g-3 bg-white p-4 shadow rounded">

        <div class="col-md-3">
            <label class="form-label">Ngày</label>
            <input type="date" name="ngay" class="form-control">
        </div>

        <div class="col-md-2">
            <label class="form-label">Tháng</label>
            <input type="number" name="thang" min="1" max="12" class="form-control">
        </div>

        <div class="col-md-2">
            <label class="form-label">Năm</label>
            <input type="number" name="nam" value="<?= date('Y') ?>" class="form-control">
        </div>

        <div class="col-md-2">
            <label class="form-label">Từ ngày</label>
            <input type="date" name="from" class="form-control">
        </div>

        <div class="col-md-2">
            <label class="form-label">Đến ngày</label>
            <input type="date" name="to" class="form-control">
        </div>

        <div class="col-md-1 d-flex align-items-end">
            <button class="btn btn-primary w-100">Lọc</button>
        </div>
    </form>

    <!-- Kết quả thống kê -->
    <div class="mt-4 p-4 bg-white shadow rounded">
        <h4>Tổng doanh thu: <span id="doanhThu" class="text-success">0 đ</span></h4>
        <h4>Tổng số đơn đặt: <span id="soDon" class="text-primary">0</span></h4>

        <hr>

        <h5 class="mb-3">Danh sách đơn đặt</h5>

        <div id="tableResult"></div>
    </div>

</div>

<script>
document.getElementById("filterForm").addEventListener("submit", async function(e){
    e.preventDefault();

    const params = new URLSearchParams(new FormData(this));
    const token = "<?= $_SESSION['token'] ?>";

    const res = await fetch("http://127.0.0.1:8000/api/owner/thong-ke?" + params.toString(), {
        headers: { "Authorization": "Bearer " + token }
    });

    const data = await res.json();

    // Cập nhật UI
    document.getElementById("doanhThu").textContent =
        new Intl.NumberFormat("vi-VN").format(data.doanh_thu) + " đ";

    document.getElementById("soDon").textContent = data.so_don;

    let html = `
        <table class="table table-bordered">
            <thead>
                <tr>
                <th>Thời gian đặt sân</th>
                    <th>Sân</th>
                    <th>Khung giờ</th>
                    <th>Giá</th>
                </tr>
            </thead>
            <tbody>
    `;

    data.lich.forEach(item => {
        html += `
            <tr>
            <td>${new Date(item.created_at).toLocaleString("vi-VN")}</td>
                <td>${item.san.ten_san}</td>
                <td>${item.gio_bat_dau} - ${item.gio_ket_thuc}</td>
                <td>${new Intl.NumberFormat("vi-VN").format(item.tong_gia)} đ</td>
            </tr>
        `;
    });

    html += "</tbody></table>";

    document.getElementById("tableResult").innerHTML = html;
});
</script>

</body>
</html>
