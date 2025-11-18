<?php
require_once '../config.php';
requireRole('customer');

$san_id = $_GET['san_id'] ?? 0;
$ngay = $_GET['ngay'] ?? '';
$gio_bat_dau = $_GET['gio_bat_dau'] ?? '';
$gio_ket_thuc = $_GET['gio_ket_thuc'] ?? '';
$gia = $_GET['gia'] ?? 0;

if (!$san_id || !$ngay || !$gio_bat_dau || !$gio_ket_thuc) {
    die("Thiếu thông tin thanh toán!");
}

// Tạo mã đơn ngẫu nhiên
$orderId = time() . rand(1000, 9999);

// Tạo đơn "đặt sân chờ thanh toán" thông qua API Laravel
$payload = [
    'san_id' => $san_id,
    'ngay_dat' => $ngay,
    'gio_bat_dau' => $gio_bat_dau,
    'gio_ket_thuc' => $gio_ket_thuc,
    'tong_gia' => $gia,
    'order_code' => $orderId,
];

$res = callAPI('POST', '/dat-san-khoi-tao', $payload, $_SESSION['token']);

if (!isset($res['dat_san_id'])) {
    die("Không tạo được đơn đặt sân!");
}

$datSanId = $res['dat_san_id'];

// ------ TẠO LINK THANH TOÁN VNPay ------ //
$vnp_TmnCode = "YOUR_TMNCODE";
$vnp_HashSecret = "YOUR_SECRET";
$vnp_ReturnUrl = "http://localhost/frontend/thanhtoan/vnpay_return.php?datSanId=$datSanId";

$vnp_Amount = $gia * 100;
$vnp_TxnRef = $orderId;
$vnp_OrderInfo = "Thanh toan dat san #$datSanId";

$inputData = [
    "vnp_Version" => "2.1.0",
    "vnp_Command" => "pay",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $vnp_Amount,
    "vnp_CurrCode" => "VND",
    "vnp_TxnRef" => $vnp_TxnRef,
    "vnp_OrderInfo" => $vnp_OrderInfo,
    "vnp_OrderType" => "billpayment",
    "vnp_Locale" => "vn",
    "vnp_ReturnUrl" => $vnp_ReturnUrl,
    "vnp_IpAddr" => $_SERVER['REMOTE_ADDR'],
    "vnp_CreateDate" => date('YmdHis')
];

ksort($inputData);
$query = "";
$hashdata = "";

foreach ($inputData as $key => $value) {
    $query .= urlencode($key) . "=" . urlencode($value) . "&";
    $hashdata .= $key . "=" . $value . "&";
}

$query = rtrim($query, "&");
$hashdata = rtrim($hashdata, "&");

$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?" . $query;
$vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
$vnp_Url .= "&vnp_SecureHash=" . $vnpSecureHash;

// CHUYỂN SANG VNPay THANH TOÁN
header("Location: $vnp_Url");
exit;
?>
