<?php
require_once '../config.php';
requireRole('owner');

$id = $_GET['id'] ?? 0;
if (!$id) {
    die('Thiếu ID sân!');
}

// 🔹 Lấy thông tin sân
$san = callAPI('GET', '/san/' . $id, null, $_SESSION['token']);
if (!$san) {
    die('Không tìm thấy sân!');
}

// 🔹 Khi người dùng bấm Lưu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'ten_san' => $_POST['ten_san'],
        'loai_san' => $_POST['loai_san'],
        'gia_thue' => $_POST['gia_thue'],
        'dia_chi' => $_POST['dia_chi'],
        'mo_ta' => $_POST['mo_ta']
    ];

    // ✅ Xử lý upload ảnh mới (nếu có)
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
        $file = $_FILES['hinh_anh'];

        // Giới hạn 5MB, chấp nhận mọi định dạng ảnh
        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = 'Ảnh quá lớn (tối đa 5MB)';
        } else {
            // 🔹 Ép ảnh về kích thước hợp lý (tự động resize)
            $tmp = $file['tmp_name'];
            $info = getimagesize($tmp);
            if ($info) {
                [$w, $h] = $info;
                $maxW = 1280;
                $maxH = 720;

                if ($w > $maxW || $h > $maxH) {
                    $ratio = min($maxW / $w, $maxH / $h);
                    $newW = (int)($w * $ratio);
                    $newH = (int)($h * $ratio);

                    $src = imagecreatefromstring(file_get_contents($tmp));
                    $dst = imagecreatetruecolor($newW, $newH);
                    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
                    imagejpeg($dst, $tmp, 85); // nén lại
                    imagedestroy($src);
                    imagedestroy($dst);
                }
            }

            // ✅ Gửi ảnh qua API (CURLFile)
            $data['hinh_anh'] = new CURLFile($file['tmp_name'], $file['type'], $file['name']);
        }
    }

    // 🔹 Gửi PUT request lên API
    $response = callAPI('POST', '/san/' . $id . '?_method=PUT', $data, $_SESSION['token']);

    if (isset($response['message'])) {
        $_SESSION['success'] = 'Cập nhật sân thành công!';
        header('Location: quan-ly-san.php');
        exit;
    } else {
        $_SESSION['error'] = $response['message'] ?? 'Cập nhật thất bại!';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa sân bóng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h3 class="mb-4">✏️ Sửa sân: <?= htmlspecialchars($san['ten_san']) ?></h3>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Tên sân</label>
            <input type="text" name="ten_san" class="form-control" required value="<?= htmlspecialchars($san['ten_san']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Loại sân</label>
            <select name="loai_san" class="form-control" required>
                <option <?= $san['loai_san'] == 'Sân 5 người' ? 'selected' : '' ?>>Sân 5 người</option>
                <option <?= $san['loai_san'] == 'Sân 7 người' ? 'selected' : '' ?>>Sân 7 người</option>
                <option <?= $san['loai_san'] == 'Sân 11 người' ? 'selected' : '' ?>>Sân 11 người</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Giá thuê (VNĐ)</label>
            <input type="number" name="gia_thue" class="form-control" required value="<?= htmlspecialchars($san['gia_thue']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Địa chỉ</label>
            <input type="text" name="dia_chi" class="form-control" required value="<?= htmlspecialchars($san['dia_chi']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <textarea name="mo_ta" class="form-control"><?= htmlspecialchars($san['mo_ta'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Ảnh sân hiện tại</label><br>
            <?php if (!empty($san['hinh_anh'])): ?>
                <img src="http://localhost:8000/storage/<?= $san['hinh_anh'] ?>" class="img-thumbnail mb-2" style="max-width: 300px;">
            <?php else: ?>
                <p class="text-muted">Chưa có ảnh nào</p>
            <?php endif; ?>
            <input type="file" name="hinh_anh" class="form-control mt-2" accept="image/*">
        </div>

        <button type="submit" class="btn btn-success">💾 Lưu thay đổi</button>
        <a href="quan-ly-san.php" class="btn btn-secondary">⬅️ Quay lại</a>
    </form>
</div>
</body>
</html>
