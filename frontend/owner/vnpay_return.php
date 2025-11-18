<?php
// Lấy tất cả tham số trả về từ VNPay
$status = isset($_GET['vnp_ResponseCode']) && $_GET['vnp_ResponseCode'] === '00' ? 'success' : 'fail';
$orderCode = $_GET['vnp_TxnRef'] ?? '';
$user_id = $_GET['user_id'] ?? null;
$amount = isset($_GET['vnp_Amount']) ? intval($_GET['vnp_Amount']) / 100 : 0;
$message = $status === 'success' ? 'Thanh toán thành công!' : ($_GET['message'] ?? 'Thanh toán thất bại!');
$transId = $_GET['vnp_TransactionNo'] ?? '';
$bankCode = $_GET['vnp_BankCode'] ?? '';
$payDate = isset($_GET['vnp_PayDate']) ? date('d/m/Y H:i', strtotime($_GET['vnp_PayDate'])) : '';

// Thực hiện lưu vào DB nếu thanh toán thành công
$saveResult = null;
if ($status === 'success' && $orderCode) {
    require_once '../config.php';
    $token = isset($_Cookie['token']) ? $_SESSION['token'] : '';
    $goiId = $_GET['goiId'] ?? null;
    if ($token && $goiId) {
        $apiUrl = 'http://127.0.0.1:8000/api/owner/check-thanh-toan/';
        $payload = json_encode(['goi_dich_vu_id' => $goiId]);
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
            <h2 class="mb-3 text-success">Thanh toán thành công!</h2>
            <p class="fs-5 mb-2">Cảm ơn bạn đã nâng cấp dịch vụ.</p>
            <?php if ($saveResult): ?>
                <?php if (!empty($saveResult['success'])): ?>
                    <div class="alert alert-success mt-3">
                        Gói <strong><?= htmlspecialchars($saveResult['data']['ten_goi'] ?? '') ?></strong> đã được kích hoạt.<br>
                        Hiệu lực từ <strong><?= htmlspecialchars($saveResult['data']['ngay_mua'] ?? '') ?></strong>
                        đến <strong><?= htmlspecialchars($saveResult['data']['ngay_het'] ?? '') ?></strong>.
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mt-3">
                        Không lưu được thông tin gói dịch vụ.<br>
                        <?= htmlspecialchars($saveResult['message'] ?? 'Vui lòng liên hệ hỗ trợ.') ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php else: ?>
            <div class="icon-fail mb-3"><i class="fas fa-times-circle"></i></div>
            <h2 class="mb-3 text-danger">Thanh toán thất bại!</h2>
            <p class="fs-5 mb-2"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <hr>
        <div class="text-start mt-3">
            <p><strong>Mã đơn hàng:</strong> <?= htmlspecialchars($orderCode) ?></p>
            <p><strong>Số tiền:</strong> <?= number_format($amount) ?>₫</p>
            <?php if ($transId): ?>
                <p><strong>Mã giao dịch VNPay:</strong> <?= htmlspecialchars($transId) ?></p>
            <?php endif; ?>
            <?php if ($bankCode): ?>
                <p><strong>Ngân hàng:</strong> <?= htmlspecialchars($bankCode) ?></p>
            <?php endif; ?>
            <?php if ($payDate): ?>
                <p><strong>Thời gian thanh toán:</strong> <?= $payDate ?></p>
            <?php endif; ?>
            <?php if ($user_id): ?>
                <p><strong>User ID:</strong> <?= htmlspecialchars($user_id) ?></p>
            <?php endif; ?>
        </div>
        <hr>
        <!-- <div class="text-start mt-3">
            <h5>Thông tin đầy đủ trả về từ VNPay:</h5>
            <table class="table table-bordered table-sm bg-white">
                <tbody>
                    <?php foreach ($_GET as $key => $val): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($key) ?></strong></td>
                            <td><?= htmlspecialchars($val) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div> -->
        <a href="goi-dich-vu.php" class="btn btn-primary mt-4"><i class="fas fa-arrow-left"></i> Quay về trang gói dịch vụ</a>
    </div>
</body>

</html>