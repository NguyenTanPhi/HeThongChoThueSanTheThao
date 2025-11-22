<?php
require_once '../config.php';
requireRole('admin');

$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';
$params = [];
if ($from) $params['from'] = $from;
if ($to)   $params['to']   = $to;

$bookingRevenue = callAPI('GET', '/admin/bao-cao/dat-san', $params, $_SESSION['token']) ?? [];
$packageRevenue = callAPI('GET', '/admin/bao-cao/goi-dich-vu', $params, $_SESSION['token']) ?? [];

$doanhThuChuSan  = array_sum(array_column($bookingRevenue, 'so_tien'));
$doanhThuHeThong = array_sum(array_column($packageRevenue, 'gia'));

// Tên file
$filename = "BaoCao_DoanhThu_" . date('d-m-Y') . ".csv";

// Header để Excel hiểu đây là file đẹp
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="BaoCao_DoanhThu_' . date('d-m-Y') . '.xls"');

// Trick: Dùng HTML + CSS trong CSV → Excel sẽ tự render đẹp như file xlsx
echo '<html><meta charset="utf-8"><body>';
echo '<style>
    table { border-collapse: collapse; width: 100%; font-family: Arial; }
    th, td { border: 1px solid #000; padding: 10px; text-align: center; }
    .title { font-size: 22px; font-weight: bold; background: #1e3799; color: white; }
    .subtitle { font-size: 16px; background: #f0f0f0; }
    .total { font-weight: bold; background: #d5f5e3; font-size: 16px; }
    .admin { background: #8e44ad; color: white; font-weight: bold; }
    .owner { background: #27ae60; color: white; font-weight: bold; }
    .number { text-align: right; font-weight: bold; color: #c0392b; }
</style>';

echo '<table>';
echo '<tr><td colspan="6" class="title">BÁO CÁO DOANH THU HỆ THỐNG</td></tr>';
echo '<tr><td colspan="6" class="subtitle">Thời gian: ' . 
     ($from ? date('d/m/Y', strtotime($from)) : 'Đầu kỳ') . ' → ' . 
     ($to ? date('d/m/Y', strtotime($to)) : 'Hôm nay') . '</td></tr>';
echo '<tr><td colspan="6"></td></tr>';

// DOANH THU HỆ THỐNG
echo '<tr><td colspan="6" class="admin">DOANH THU HỆ THỐNG (Gói dịch vụ - Tiền của Admin)</td></tr>';
echo '<tr><td colspan="3">Tổng tiền</td><td colspan="3" class="number">' . number_format($doanhThuHeThong) . ' ₫</td></tr>';
echo '<tr><td colspan="3">Số giao dịch</td><td colspan="3">' . count($packageRevenue) . ' giao dịch</td></tr>';
echo '<tr><td colspan="6"></td></tr>';

// DOANH THU CHỦ SÂN
echo '<tr><td colspan="6" class="owner">DOANH THU CHỦ SÂN (Đặt sân - Tiền của chủ sân)</td></tr>';
echo '<tr><td colspan="3">Tổng tiền</td><td colspan="3" class="number">' . number_format($doanhThuChuSan) . ' ₫</td></tr>';
echo '<tr><td colspan="3">Số lượt đặt</td><td colspan="3">' . count($bookingRevenue) . ' lượt</td></tr>';
echo '<tr><td colspan="6"></td></tr>';

// CHI TIẾT ĐẶT SÂN
echo '<tr><td colspan="6" style="background:#34495e;color:white;font-weight:bold;">CHI TIẾT ĐẶT SÂN</td></tr>';
echo '<tr style="background:#2c3e50;color:white;font-weight:bold;">
        <td>ID</td><td>Tên sân</td><td>Người đặt</td><td>Ngày</td><td>Giờ chơi</td><td>Số tiền</td>
      </tr>';

foreach ($bookingRevenue as $b) {
    echo '<tr>
        <td>' . $b['dat_san_id'] . '</td>
        <td>' . htmlspecialchars($b['ten_san']) . '</td>
        <td>' . htmlspecialchars($b['nguoi_dat']) . '</td>
        <td>' . date('d/m/Y', strtotime($b['ngay_dat'])) . '</td>
        <td>' . substr($b['gio_bat_dau'],0,5) . '-' . substr($b['gio_ket_thuc'],0,5) . '</td>
        <td class="number">' . number_format($b['so_tien']) . ' ₫</td>
    </tr>';
}
echo '<tr class="total"><td colspan="5">TỔNG DOANH THU CHỦ SÂN</td><td class="number">' . number_format($doanhThuChuSan) . ' ₫</td></tr>';
echo '<tr><td colspan="6"></td></tr>';

// CHI TIẾT GÓI DỊCH VỤ
echo '<tr><td colspan="6" style="background:#9b59b6;color:white;font-weight:bold;">CHI TIẾT GÓI DỊCH VỤ</td></tr>';
echo '<tr style="background:#8e44ad;color:white;font-weight:bold;">
        <td>ID</td><td>Tên gói</td><td>Người mua</td><td>Ngày mua</td><td>Hết hạn</td><td>Giá</td>
      </tr>';

foreach ($packageRevenue as $p) {
    echo '<tr>
        <td>' . $p['id'] . '</td>
        <td>' . htmlspecialchars($p['ten_goi']) . '</td>
        <td>' . htmlspecialchars($p['nguoi_dung']) . '</td>
        <td>' . date('d/m/Y', strtotime($p['ngay_mua'])) . '</td>
        <td>' . date('d/m/Y', strtotime($p['ngay_het'])) . '</td>
        <td class="number">' . number_format($p['gia']) . ' ₫</td>
    </tr>';
}
echo '<tr class="total"><td colspan="5">TỔNG DOANH THU HỆ THỐNG</td><td class="number">' . number_format($doanhThuHeThong) . ' ₫</td></tr>';

echo '</table>';
echo '<br><p style="text-align:center;color:#7f8c8d;">Xuất lúc: ' . date('H:i d/m/Y') . ' - Hệ thống đặt sân bóng</p>';
echo '</body></html>';

exit;