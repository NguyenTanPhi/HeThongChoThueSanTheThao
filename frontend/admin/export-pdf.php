<?php
require_once '../config.php';
requireRole('admin');

$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';
$params = [];
if ($from) $params['from'] = $from;
if ($to) $params['to'] = $to;

$bookingRevenue = callAPI('GET', '/admin/bao-cao/dat-san', $params, $_SESSION['token']) ?? [];
$packageRevenue = callAPI('GET', '/admin/bao-cao/goi-dich-vu', $params, $_SESSION['token']) ?? [];

$doanhThuChuSan  = array_sum(array_column($bookingRevenue, 'so_tien'));
$doanhThuHeThong = array_sum(array_column($packageRevenue, 'gia'));

// Tạo HTML đẹp cho PDF (tiếng Việt chuẩn, có màu, bảng)
$html = '
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo doanh thu</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; color: #333; line-height: 1.6; }
        h1 { text-align: center; color: #1e3799; font-size: 28px; margin-bottom: 10px; border-bottom: 3px solid #1e3799; padding-bottom: 10px; }
        .info { text-align: center; font-size: 16px; margin: 20px 0; color: #555; }
        h2 { background: linear-gradient(135deg, #8e44ad, #9b59b6); color: white; padding: 15px; border-radius: 10px; margin: 30px 0 15px; font-size: 20px; }
        h3 { background: linear-gradient(135deg, #27ae60, #2ecc71); color: white; padding: 12px; border-radius: 8px; margin: 25px 0 10px; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        th { background: #2c3e50; color: white; padding: 12px; text-align: center; font-weight: bold; }
        td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        .money { text-align: right; font-weight: bold; color: #e74c3c; font-size: 16px; }
        .total { background: #f8f9fa; font-weight: bold; font-size: 15px; border-top: 2px solid #27ae60; }
        .footer { text-align: center; margin-top: 50px; color: #95a5a6; font-size: 12px; border-top: 1px solid #eee; padding-top: 20px; }
        @media print { body { margin: 0; } }
    </style>
</head>
<body>
    <h1>BÁO CÁO DOANH THU HỆ THỐNG</h1>
    <div class="info">
        Thời gian: ' . ($from ? date('d/m/Y', strtotime($from)) : 'Đầu kỳ') . ' → ' . ($to ? date('d/m/Y', strtotime($to)) : 'Hôm nay') . '
    </div>

    <h2>DOANH THU HỆ THỐNG (Gói dịch vụ - Thuộc về Admin)</h2>
    <table>
        <tr><th width="70%">Mô tả</th><th>Số liệu</th></tr>
        <tr><td><strong>Tổng doanh thu</strong></td><td class="money">' . number_format($doanhThuHeThong) . ' ₫</td></tr>
        <tr><td>Số giao dịch mua gói</td><td>' . count($packageRevenue) . ' giao dịch</td></tr>
    </table>

    <h3>DOANH THU CHỦ SÂN (Đặt sân - Thuộc về chủ sân)</h3>
    <table>
        <tr><th width="70%">Mô tả</th><th>Số liệu</th></tr>
        <tr><td><strong>Tổng doanh thu</strong></td><td class="money">' . number_format($doanhThuChuSan) . ' ₫</td></tr>
        <tr><td>Số lượt đặt sân</td><td>' . count($bookingRevenue) . ' lượt</td></tr>
    </table>

    <h2>CHI TIẾT ĐẶT SÂN</h2>
    <table>
        <tr><th>ID</th><th>Tên sân</th><th>Người đặt</th><th>Ngày</th><th>Giờ chơi</th><th>Số tiền</th></tr>';

foreach ($bookingRevenue as $b) {
    $html .= '<tr>
        <td>' . $b['dat_san_id'] . '</td>
        <td>' . htmlspecialchars($b['ten_san']) . '</td>
        <td>' . htmlspecialchars($b['nguoi_dat']) . '</td>
        <td>' . date('d/m/Y', strtotime($b['ngay_dat'])) . '</td>
        <td>' . substr($b['gio_bat_dau'], 0, 5) . ' - ' . substr($b['gio_ket_thuc'], 0, 5) . '</td>
        <td class="money">' . number_format($b['so_tien']) . ' ₫</td>
    </tr>';
}
$html .= '<tr class="total"><td colspan="5"><strong>TỔNG DOANH THU CHỦ SÂN</strong></td>
          <td class="money">' . number_format($doanhThuChuSan) . ' ₫</td></tr>
    </table>

    <h2>CHI TIẾT GÓI DỊCH VỤ</h2>
    <table>
        <tr><th>ID</th><th>Tên gói</th><th>Người mua</th><th>Ngày mua</th><th>Hết hạn</th><th>Giá</th></tr>';

foreach ($packageRevenue as $p) {
    $html .= '<tr>
        <td>' . $p['id'] . '</td>
        <td>' . htmlspecialchars($p['ten_goi']) . '</td>
        <td>' . htmlspecialchars($p['nguoi_dung']) . '</td>
        <td>' . date('d/m/Y', strtotime($p['ngay_mua'])) . '</td>
        <td>' . date('d/m/Y', strtotime($p['ngay_het'])) . '</td>
        <td class="money">' . number_format($p['gia']) . ' ₫</td>
    </tr>';
}
$html .= '<tr class="total"><td colspan="5"><strong>TỔNG DOANH THU HỆ THỐNG</strong></td>
          <td class="money">' . number_format($doanhThuHeThong) . ' ₫</td></tr>
    </table>

    <div class="footer">
        Báo cáo được xuất lúc ' . date('H:i d/m/Y') . '<br>
        Hệ thống đặt sân bóng - © 2025
    </div>
</body>
</html>';

// === XUẤT PDF KHÔNG CẦN THƯ VIỆN (DÙNG BROWSER PRINT TO PDF) ===
header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: inline; filename="BaoCao_DoanhThu_' . date('d-m-Y') . '.html"');

// Thêm script tự động mở hộp thoại in PDF
echo $html;
echo '<script>
    window.onload = function() {
        setTimeout(function() {
            window.print(); // Mở hộp thoại in → chọn "Save as PDF"
        }, 1000);
    };
</script>';
exit;