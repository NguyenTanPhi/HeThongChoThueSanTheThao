<?php
require_once '../config.php';
requireRole('owner');
$user = getUser();

if ($_POST) {
    $hasError = false;
    $_SESSION['old'] = $_POST;

    $hinh_anh = null;
$hinh_anh = null;
if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
    $file = $_FILES['hinh_anh'];
    $tmpPath = $file['tmp_name'];
    $originalName = $file['name'];
    $fileType = mime_content_type($tmpPath);

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // Lấy phần mở rộng từ tên file gốc
    $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions)) {
        $_SESSION['error'] = 'Chỉ chấp nhận file: JPG, PNG, GIF, WEBP.';
        $hasError = true;
    } elseif (!in_array($fileType, $allowedTypes)) {
        $_SESSION['error'] = 'File không phải định dạng ảnh hợp lệ.';
        $hasError = true;
    } else {
        // Nếu ảnh > 2MB → nén
        if ($file['size'] > 2 * 1024 * 1024) {
            $imageInfo = getimagesize($tmpPath);
            if (!$imageInfo) {
                $_SESSION['error'] = 'Không thể đọc thông tin ảnh.';
                $hasError = true;
            } else {
                list($width, $height) = $imageInfo;

                // Giảm 70%
                $newWidth = $width * 0.7;
                $newHeight = $height * 0.7;

                // Tạo ảnh nguồn
                switch ($fileType) {
                    case 'image/jpeg': $src = imagecreatefromjpeg($tmpPath); break;
                    case 'image/png':  $src = imagecreatefrompng($tmpPath); break;
                    case 'image/webp': $src = imagecreatefromwebp($tmpPath); break;
                    case 'image/gif':  $src = imagecreatefromgif($tmpPath); break;
                    default: $src = false;
                }

                if (!$src) {
                    $_SESSION['error'] = 'Không hỗ trợ định dạng ảnh này để nén.';
                    $hasError = true;
                } else {
                    $dst = imagecreatetruecolor($newWidth, $newHeight);
                    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                    // Lưu vào file tạm với đúng định dạng
                    $tempFile = tempnam(sys_get_temp_dir(), 'img_') . '.' . $fileExtension;
                    $saveSuccess = false;

                    switch ($fileExtension) {
                        case 'jpg':
                        case 'jpeg':
                            $saveSuccess = imagejpeg($dst, $tempFile, 75);
                            break;
                        case 'png':
                            $saveSuccess = imagepng($dst, $tempFile, 6); // 0-9, 6 là cân bằng
                            break;
                        case 'gif':
                            $saveSuccess = imagegif($dst, $tempFile);
                            break;
                        case 'webp':
                            $saveSuccess = imagewebp($dst, $tempFile, 75);
                            break;
                    }

                    imagedestroy($src);
                    imagedestroy($dst);

                    if ($saveSuccess && file_exists($tempFile)) {
                        $tmpPath = $tempFile;
                        $fileType = $fileType; // giữ nguyên MIME gốc
                    } else {
                        $_SESSION['error'] = 'Lỗi khi nén ảnh. Vui lòng thử lại.';
                        $hasError = true;
                    }
                }
            }
        }

        // Tạo CURLFile chỉ khi không lỗi
                // Tạo CURLFile CHUẨN LARAVEL (giữ nguyên chức năng cũ)
        if (!$hasError && isset($tmpPath) && file_exists($tmpPath)) {
            $realPath = realpath($tmpPath);

            // Chuẩn hóa MIME cho Laravel
            $mimeMap = [
                'image/jpeg' => 'image/jpeg',
                'image/jpg'  => 'image/jpeg',
                'image/png'  => 'image/png',
                'image/gif'  => 'image/gif',
                'image/webp' => 'image/webp',
            ];
            $finalMime = $mimeMap[$fileType] ?? 'image/jpeg';

            // Chuẩn hóa tên file (giữ tên gốc, chỉ làm sạch)
            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $safeName .= '.jpg';
            }

            $hinh_anh = new CURLFile($realPath, $finalMime);
            $hinh_anh->setPostFilename($safeName); // QUAN TRỌNG NHẤT
        }
    }

}


    if (!$hasError) {
        $postData = [
            'ten_san'   => trim($_POST['ten_san'] ?? ''),
            'loai_san'  => $_POST['loai_san'] ?? '',
            'gia_thue'  => $_POST['gia_thue'] ?? '',
            'dia_chi'   => trim($_POST['dia_chi'] ?? ''),
            'mo_ta'     => trim($_POST['mo_ta'] ?? ''),
        ];
        if ($hinh_anh) $postData['hinh_anh'] = $hinh_anh;

        $response_raw = callAPI('POST', '/owner/san', $postData, $_SESSION['token']);

        // FIX CHÍNH: ĐẢM BẢO $response LUÔN LÀ STRING
        $response = is_array($response_raw) ? ($response_raw['body'] ?? json_encode($response_raw)) : $response_raw;
        $response = is_string($response) ? $response : json_encode($response);

        $json = json_decode($response, true);

        if (json_last_error() === JSON_ERROR_NONE && $json !== null) {
            if (isset($json['success']) || (isset($json['message']) && stripos($json['message'], 'thành công') !== false)) {
                $_SESSION['success'] = 'Gửi yêu cầu mở sân thành công! Vui lòng chờ quản trị viên duyệt trong vòng 24h.';
            }
            elseif (isset($json['require_package']) || isset($json['package_message'])) {
                $_SESSION['show_package_popup'] = true;
                $_SESSION['package_msg'] = $json['package_message'] ?? 'Bạn cần mua hoặc gia hạn gói dịch vụ để thêm sân mới.';
            }
            elseif (isset($json['errors'])) {
                $errs = [];
                foreach ($json['errors'] as $field => $msgs) {
                    foreach ((array)$msgs as $msg) $errs[] = $msg;
                }
                $_SESSION['error'] = '<strong>Lỗi nhập liệu:</strong><br>• ' . implode('<br>• ', $errs);
            }
            else {
                $_SESSION['error'] = $json['message'] ?? 'Lỗi không xác định từ server.';
            }
        }
        else {
            if (stripos($response, 'thành công') !== false || stripos($response, 'success') !== false) {
                $_SESSION['success'] = 'Gửi yêu cầu mở sân thành công! Vui lòng chờ quản trị viên duyệt trong vòng 24h.';
            }
            elseif (stripos($response, 'require_package') !== false || stripos($response, 'gói') !== false) {
                $_SESSION['show_package_popup'] = true;
                $_SESSION['package_msg'] = 'Bạn cần mua hoặc gia hạn gói dịch vụ để thêm sân mới.';
            }
            else {
                $_SESSION['error'] = 'Lỗi không xác định từ server. Vui lòng thử lại sau.';
            }
        }

        $_SESSION['trigger_swal'] = true;
    }
}
?>

<!-- HTML + SweetAlert giữ nguyên như file trước -->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sân mới</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CSS giữ nguyên -->
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); padding: 20px; min-height: 100vh; margin: 0; }
        .container { max-width: 850px; margin: 40px auto; background: white; padding: 45px; border-radius: 25px; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
        h2 { text-align: center; font-size: 2.8rem; margin-bottom: 10px; background: linear-gradient(45deg, #27ae60, #2ecc71); background-clip: text; color: transparent; font-weight: 900; }
        .greeting { text-align: center; color: #7f8c8d; font-size: 1.3rem; margin-bottom: 35px; }
        .form-group { margin-bottom: 28px; }
        label { display: block; margin-bottom: 12px; font-weight: bold; color: #2c3e50; font-size: 1.15rem; }
        input, select, textarea { width: 100%; padding: 16px; border: 2px solid #e0e0e0; border-radius: 15px; font-size: 16px; background: #f8f9fa; box-sizing: border-box; }
        input:focus, select:focus, textarea:focus { border-color: #27ae60; outline: none; box-shadow: 0 0 0 4px rgba(39,174,96,0.2); }
        textarea { height: 130px; resize: vertical; }
        .btn { background: linear-gradient(45deg, #27ae60, #2ecc71); color: white; padding: 20px 50px; border: none; border-radius: 50px; font-size: 1.4rem; cursor: pointer; display: block; margin: 40px auto; box-shadow: 0 12px 35px rgba(39,174,96,0.4); font-weight: bold; transition: all 0.3s; }
        .btn:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(39,174,96,0.6); }
        .preview-img { max-width: 100%; max-height: 380px; margin-top: 15px; border-radius: 20px; display: none; box-shadow: 0 15px 40px rgba(0,0,0,0.2); border: 4px solid #27ae60; }
        .back-link { display: block; text-align: center; margin-top: 35px; color: #667eea; font-weight: bold; font-size: 1.2rem; text-decoration: none; }
        .back-link:hover { color: #764ba2; }
    </style>
</head>
<body>
<div class="container">
    <h2>THÊM SÂN MỚI</h2>
    <p class="greeting">Xin chào <strong style="color:#27ae60"><?= htmlspecialchars($user['name']) ?></strong>! Tạo sân bóng ngay hôm nay!</p>

    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>Tên sân  *</label>
            <input type="text" name="ten_san" required placeholder="VD: Sân bóng Nam Sport" value="<?= htmlspecialchars($_SESSION['old']['ten_san'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Loại sân *</label>
            <select name="loai_san" required>
                <option value="">-- Chọn loại sân --</option>
                <option value="Sân 5 người" <?= ($_SESSION['old']['loai_san'] ?? '') == 'Sân 5 người' ? 'selected' : '' ?>>Sân 5 người</option>
                <option value="Sân 7 người" <?= ($_SESSION['old']['loai_san'] ?? '') == 'Sân 7 người' ? 'selected' : '' ?>>Sân 7 người</option>
                <option value="Sân 11 người" <?= ($_SESSION['old']['loai_san'] ?? '') == 'Sân 11 người' ? 'selected' : '' ?>>Sân 11 người</option>
                <option value="Sân Tenis" <?= ($_SESSION['old']['loai_san'] ?? '') == 'Sân Tenis' ? 'selected' : '' ?>>Sân Tenis</option>
                <option value="Sân Cầu lông" <?= ($_SESSION['old']['loai_san'] ?? '') == 'Sân Cầu lông' ? 'selected' : '' ?>>Sân cầu lông</option>
                <option value="Sân Bóng chuyền" <?= ($_SESSION['old']['loai_san'] ?? '') == 'Sân Bóng chuyền' ? 'selected' : '' ?>>Sân Bóng chuyền</option>
                <option value="Sân PickelBall" <?= ($_SESSION['old']['loai_san'] ?? '') == 'Sân PickelBall' ? 'selected' : '' ?>>Sân PickelBall</option>
            </select>
        </div>
        <div class="form-group">
            <label>Giá thuê / giờ (VNĐ) *</label>
            <input type="number" name="gia_thue" required min="100000" placeholder="VD: 500000" value="<?= htmlspecialchars($_SESSION['old']['gia_thue'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Địa chỉ sân *</label>
            <input type="text" name="dia_chi" required placeholder="VD: 123 Đường Láng, Quận Đống Đa, Hà Nội" value="<?= htmlspecialchars($_SESSION['old']['dia_chi'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Mô tả chi tiết</label>
            <textarea name="mo_ta" placeholder="Cỏ nhân tạo, có đèn, nhà vệ sinh..."><?= htmlspecialchars($_SESSION['old']['mo_ta'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Ảnh sân (tối đa 2MB)</label>
            <input type="file" name="hinh_anh" accept="image/*" onchange="previewImage(this)">
            <img id="preview" class="preview-img" src="" alt="Preview">
        </div>
        <button type="submit" class="btn">GỬI YÊU CẦU MỞ SÂN</button>
    </form>
    <a href="quan-ly-san.php" class="back-link">Quay lại quản lý sân</a>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('preview').style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['trigger_swal'])): ?>
        <?php if (isset($_SESSION['success'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'THÀNH CÔNG!',
                html: `<strong style='font-size:1.4rem;color:#27ae60'><?= htmlspecialchars($_SESSION['success']) ?></strong>`,
                timer: 6000,
                timerProgressBar: true,
                showConfirmButton: false
            });
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'CÓ LỖI!',
                html: `<div style='text-align:left;'><?= $_SESSION['error'] ?></div>`,
                confirmButtonText: 'Đã hiểu',
                confirmButtonColor: '#e74c3c'
            });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['show_package_popup'])): ?>
            Swal.fire({
                icon: 'warning',
                title: 'Cần gói dịch vụ!',
                html: `<div style='text-align:left;font-size:1.3rem;line-height:2.3;'>
                           <strong style='font-size:1.5rem;color:#f39c12'><?= htmlspecialchars($_SESSION['package_msg']) ?></strong><br><br>
                           Không giới hạn số sân<br>Duyệt siêu nhanh 15-30 phút<br>Hỗ trợ 24/7<br>Hiển thị nổi bật
                       </div>`,
                confirmButtonText: 'MUA GÓI NGAY',
                cancelButtonText: 'Để sau',
                showCancelButton: true,
                allowOutsideClick: false,
                width: '680px'
            }).then((r) => {
                if (r.isConfirmed) window.location.href = 'goi-dich-vu.php';
                else window.location.href = 'quan-ly-san.php';
            });
            <?php unset($_SESSION['show_package_popup'], $_SESSION['package_msg']); ?>
        <?php endif; ?>

        <?php unset($_SESSION['trigger_swal']); ?>
    <?php endif; ?>
});
</script>

<?php unset($_SESSION['old']); ?>
</body>
</html>