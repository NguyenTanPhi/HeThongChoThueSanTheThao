<?php
require_once '../config.php';
requireRole('customer');
$user = getUser();

$san_id = $_GET['san_id'] ?? 0;
if ($san_id <= 0) {
    die('<div class="alert alert-danger text-center p-5">Không tìm thấy sân bóng!</div></body></html>');
}

$san = callAPI('GET', "/san/{$san_id}", null, $_SESSION['token']);
if (empty($san['ten_san'] ?? null)) {
    die('<div class="alert alert-danger text-center p-5">Sân bóng không tồn tại!</div></body></html>');
}
$san = is_array($san) ? $san : (array)$san;

$check = callAPI('GET', "/danh-gia/check?nguoi_dung_id={$user['id']}&san_id={$san_id}", null, $_SESSION['token']);
$daDanhGia = !empty($check['da_danh_gia']);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đánh giá sân - <?=htmlspecialchars($san['ten_san'])?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {background:linear-gradient(135deg,#f5f7fa,#c3cfe2);font-family:'Segoe UI',sans-serif;min-height:100vh;margin:0}
        .review-card {background:#fff;border-radius:20px;box-shadow:0 15px 40px rgba(0,0,0,.12);max-width:650px;margin:3rem auto;padding:3rem}
        .rating {direction:rtl;display:inline-block;font-size:3.8rem;letter-spacing:10px}
        .rating input {display:none}
        .rating label {color:#ddd;cursor:pointer;transition:.3s}
        .rating label:hover,
        .rating label:hover ~ label,
        .rating input:checked ~ label {color:#ffc107 !important;transform:scale(1.15)}
        .btn-submit {background:linear-gradient(135deg,#667eea,#764ba2);border:none;padding:15px 60px;font-size:1.3rem;border-radius:50px;color:#fff;font-weight:bold}
        .btn-submit:hover {transform:translateY(-3px);box-shadow:0 15px 30px rgba(102,126,234,.4)}
        .btn-submit:disabled {opacity:0.7;transform:none}

        /* TOAST THÀNH CÔNG SIÊU ĐẸP */
        .toast-success {
            position: fixed;top: 20px;right: 20px;z-index: 9999;
            background: linear-gradient(135deg,#28a745,#20c997);
            color: white;padding: 18px 30px;border-radius: 15px;
            box-shadow: 0 10px 30px rgba(40,167,69,.4);
            transform: translateX(400px);opacity: 0;transition: all .6s cubic-bezier(0.68,-0.55,0.27,1.55);
            font-size: 1.1rem;font-weight: bold;display: flex;align-items: center;gap: 12px;
        }
        .toast-success.show {transform: translateX(0);opacity: 1}
        .toast-success i {font-size: 2rem}
    </style>
</head>
<body>

<div class="container">
    <div class="review-card text-center">
        <h2 class="mb-2 fw-bold">Đánh giá sân bóng</h2>
        <h4 class="text-primary mb-1"><?=htmlspecialchars($san['ten_san'])?></h4>
        <p class="text-muted mb-4">
            <i class="bi bi-geo-alt-fill"></i> <?=htmlspecialchars($san['dia_chi'] ?? 'Không có địa chỉ')?>
        </p>

        <?php if ($daDanhGia): ?>
            <div class="alert alert-success rounded-4 p-4 fs-4 shadow-sm">
                <i class="bi bi-check-circle-fill"></i><br>
                Bạn đã đánh giá sân này rồi!<br>
                <strong>Cảm ơn phản hồi của bạn!</strong>
            </div>
            <a href="lich-su-dat-san.php" class="btn btn-outline-secondary btn-lg px-5 mt-3 rounded-pill">
                Quay lại lịch sử
            </a>

        <?php else: ?>
            <form id="reviewForm" onsubmit="return false;">
                <div class="mb-5">
                    <div class="rating">
                        <input type="radio" name="diem" value="5" id="star5" required><label for="star5" title="Tuyệt vời">★</label>
                        <input type="radio" name="diem" value="4" id="star4"><label for="star4" title="Tốt">★</label>
                        <input type="radio" name="diem" value="3" id="star3"><label for="star3" title="Bình thường">★</label>
                        <input type="radio" name="diem" value="2" id="star2"><label for="star2" title="Tạm được">★</label>
                        <input type="radio" name="diem" value="1" id="star1"><label for="star1" title="Kém">★</label>
                    </div>
                    <p class="mt-3 text-muted">Chọn số sao bạn muốn đánh giá</p>
                </div>

                <div class="mb-4">
                    <textarea class="form-control shadow-sm" rows="6" id="noi_dung" 
                        placeholder="Chia sẻ trải nghiệm của bạn về sân, dịch vụ, nhân viên... (tối thiểu 10 ký tự)" 
                        required minlength="10"></textarea>
                </div>

                <button type="submit" id="btnSubmit" class="btn btn-submit shadow-lg">
                    <span class="normal">Gửi đánh giá</span>
                    <span class="loading" style="display:none">Đang gửi...</span>
                </button>

                <div id="errorMsg" class="alert alert-danger mt-3 rounded-4" style="display:none"></div>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- TOAST THÀNH CÔNG -->
<div id="toastSuccess" class="toast-success">
    <i class="bi bi-check-circle-fill"></i>
    <div>
        <strong>Đánh giá thành công!</strong><br>
        Cảm ơn bạn đã góp ý
    </div>
</div>

<script>
document.getElementById('reviewForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    document.getElementById('errorMsg').style.display = 'none';

    const diem = document.querySelector('input[name="diem"]:checked')?.value;
    if (!diem) return showError('Vui lòng chọn số sao!');

    const noi_dung = document.getElementById('noi_dung').value.trim();
    if (noi_dung.length < 10) return showError('Vui lòng viết ít nhất 10 ký tự!');

    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.querySelector('.normal').style.display = 'none';
    btn.querySelector('.loading').style.display = 'inline';

    try {
        const response = await fetch('<?= API_URL ?>/danh-gia', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer <?= $_SESSION['token'] ?>',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                san_id: <?= $san_id ?>,
                diem_danh_gia: parseInt(diem),
                noi_dung: noi_dung
            })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // HIỆN TOAST ĐẸP THAY VÌ ALERT
            document.getElementById('toastSuccess').classList.add('show');
            setTimeout(() => {
                location.href = 'lich-su-dat.php';
            }, 2000); // Chuyển trang sau 2 giây
        } else {
            showError(data.message || 'Có lỗi xảy ra, vui lòng thử lại!');
        }
    } catch (err) {
        showError('Không thể kết nối đến server. Vui lòng thử lại!');
    } finally {
        btn.disabled = false;
        btn.querySelector('.normal').style.display = 'inline';
        btn.querySelector('.loading').style.display = 'none';
    }
});

function showError(msg) {
    const el = document.getElementById('errorMsg');
    el.textContent = msg;
    el.style.display = 'block';
}
</script>

</body>
</html>