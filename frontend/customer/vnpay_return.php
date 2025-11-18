<?php
require_once '../config.php';

// Trạng thái thanh toán VNPay
$status = (isset($_GET['vnp_ResponseCode']) && $_GET['vnp_ResponseCode'] === '00') ? 'success' : 'fail';

$orderCode = $_GET['vnp_TxnRef'] ?? '';
$user_id = $_GET['user_id'] ?? null;
$datSanId = $_GET['datSanId'] ?? null; // 🔥 đổi từ goiId
$amount = isset($_GET['vnp_Amount']) ? intval($_GET['vnp_Amount']) / 100 : 0;

$message = $status === 'success'
    ? 'Thanh toán đặt sân thành công!'
    : ($_GET['message'] ?? 'Thanh toán thất bại!');

$transId = $_GET['vnp_TransactionNo'] ?? '';
$bankCode = $_GET['vnp_BankCode'] ?? '';
$payDate = isset($_GET['vnp_PayDate']) ? date('d/m/Y H:i', strtotime($_GET['vnp_PayDate'])) : '';

$saveResult = null;

// Nếu thanh toán THÀNH CÔNG → gửi API xác nhận cho backend
if ($status === 'success' && $orderCode && $datSanId) {

    $token = $_SESSION['token'] ?? '';

    if ($token) {

        // 🔥 endpoint cho đặt sân, bạn thay theo backend bạn đang dùng
        $apiUrl = API_URL . '/customer/check-thanh-toan/' . $orderCode;

        $payload = json_encode([
            'dat_san_id' => $datSanId,
            'amount' => $amount,
            'payment_method' => 'vnpay',
            'vnp_transaction_no' => $transId
        ]);

        $ch = curl_init($apiUrl);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        $saveResult = json_decode($result, true);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Kết quả thanh toán VNPay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8fafc;
        }

        .result-card {
            max-width: 500px;
            margin: 60px auto;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        }

        .icon-success {
            color: #28a745;
            font-size: 4rem;
        }

        .icon-fail {
            color: #dc3545;
            font-size: 4rem;
        }
    </style>
</head>

<body>
    <div class="card result-card p-4 text-center">

        <?php if ($status === 'success'): ?>
            <div class="icon-success mb-3"><i class="fas fa-check-circle"></i></div>
            <h2 class="mb-3 text-success">Thanh toán đặt sân thành công!</h2>
            <p class="fs-5 mb-2">Bạn đã đặt sân thành công. Chúc bạn có buổi chơi vui vẻ!</p>

            <?php if ($saveResult && !empty($saveResult['success'])): ?>
                <div class="alert alert-success mt-3">
                    <strong>Đặt sân đã được xác nhận.</strong><br>
                    Trạng thái: <?= htmlspecialchars($saveResult['data']['trang_thai'] ?? 'Đã thanh toán') ?><br>
                </div>
            <?php elseif ($saveResult): ?>
                <div class="alert alert-warning mt-3">
                    Không lưu được thông tin thanh toán.<br>
                    <?= htmlspecialchars($saveResult['message'] ?? 'Vui lòng liên hệ hỗ trợ.') ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="icon-fail mb-3"><i class="fas fa-times-circle"></i></div>
            <h2 class="mb-3 text-danger">Thanh toán thất bại!</h2>
            <p class="fs-5 mb-2"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <hr>

        <div class="text-start mt-3">
            <p><strong>Mã đơn đặt sân:</strong> <?= htmlspecialchars($orderCode) ?></p>
            <p><strong>Số tiền:</strong> <?= number_format($amount) ?>₫</p>
            <?php if ($transId): ?><p><strong>Mã giao dịch:</strong> <?= htmlspecialchars($transId) ?></p><?php endif; ?>
            <?php if ($bankCode): ?><p><strong>Ngân hàng:</strong> <?= htmlspecialchars($bankCode) ?></p><?php endif; ?>
            <?php if ($payDate): ?><p><strong>Thời gian thanh toán:</strong> <?= $payDate ?></p><?php endif; ?>
        </div>

        <hr>

        <a href="lich-su-dat-san.php" class="btn btn-primary mt-4">
            <i class="fas fa-arrow-left"></i> Quay về lịch sử đặt sân
        </a>
    </div>
</body>

</html>