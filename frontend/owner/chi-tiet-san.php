<?php
require_once '../config.php';
requireRole('owner');

$id = $_GET['id'] ?? 0;
if (!$id) die('<div class="alert alert-danger text-center p-5">Thiếu ID sân!</div>');

$san = callAPI('GET', '/san/' . $id, null, $_SESSION['token']);
if (!$san || !isset($san['id'])) die('<div class="alert alert-danger text-center p-5">Không tìm thấy sân!</div>');

// LẤY ĐÁNH GIÁ
$danhGiaRes = callAPI('GET', '/danh-gia/san/' . $id, null, $_SESSION['token']);

$danhGiaList = [];
$trungBinh = 0;
$tongSo = 0;

if ($danhGiaRes && is_array($danhGiaRes)) {
    if (isset($danhGiaRes['danh_gia'])) {
        $danhGiaList = $danhGiaRes['danh_gia'];
        $trungBinh = round($danhGiaRes['trung_binh'] ?? 0, 1);
        $tongSo = $danhGiaRes['tong_so'] ?? count($danhGiaList);
    } elseif (isset($danhGiaRes['data'])) {
        $danhGiaList = $danhGiaRes['data'];
        $trungBinh = round($danhGiaRes['avg'] ?? 0, 1);
        $tongSo = count($danhGiaList);
    } elseif (is_array($danhGiaRes)) {
        $danhGiaList = $danhGiaRes;
        $tongSo = count($danhGiaList);
        if ($tongSo > 0) {
            $sum = 0;
            foreach ($danhGiaList as $dg) {
                $sao = $dg['so_sao'] ?? ($dg->so_sao ?? 0);
                $sum += (float)$sao;
            }
            $trungBinh = round($sum / $tongSo, 1);
        }
    }
}

$user = getUser();

// Hàm render sao đẹp
function renderStars($rating): string {
    $full  = floor($rating);
    $half  = ($rating - $full >= 0.5) ? 1 : 0;
    $empty = 5 - $full - $half;
    $html = str_repeat('<i class="bi bi-star-fill text-warning"></i>', $full);
    $html .= $half ? '<i class="bi bi-star-half text-warning"></i>' : '';
    $html .= str_repeat('<i class="bi bi-star text-muted"></i>', $empty);
    return $html;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($san['ten_san']) ?> - Chi tiết sân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root { --primary: #fd7e14; --primary-dark: #e67e22; }
        body {
            background: linear-gradient(135deg, #fff8f0 0%, #ffe0b3 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        .navbar-owner {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            box-shadow: 0 4px 20px rgba(253,126,20,0.4);
            padding: 1rem 0;
        }
        .card-main {
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.18);
            background: white;
        }
        .san-img {
            width: 100%;
            height: 520px;
            object-fit: cover;
            border-bottom: 6px solid var(--primary);
        }
        .rating-box {
            position: absolute;
            top: 25px;
            left: 25px;
            background: rgba(0,0,0,0.85);
            color: white;
            padding: 18px 26px;
            border-radius: 20px;
            text-align: center;
            backdrop-filter: blur(12px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.5);
            font-weight: bold;
            z-index: 10;
        }
        .rating-stars { font-size: 2.1rem; margin-bottom: 8px; }
        .rating-box strong { font-size: 2.8rem; font-weight: 900; display: block; line-height: 1; }
        .rating-count { font-size: 1rem; opacity: 0.9; }

        .review-card {
            background: white;
            border-radius: 22px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid #eee;
            transition: all 0.3s ease;
        }
        .review-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 45px rgba(0,0,0,0.16);
        }
        .avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.8rem;
            flex-shrink: 0;
        }
        .no-review {
            text-align: center;
            padding: 5rem 2rem;
            color: #8e8e8e;
        }
        .no-review i {
            font-size: 5.5rem;
            opacity: 0.3;
            margin-bottom: 1.5rem;
        }
        .badge-status {
            font-size: 1.1rem;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
        }
        @media (max-width: 768px) {
            .san-img { height: 300px; }
            .rating-box { top: 15px; left: 15px; padding: 14px 18px; }
            .rating-box strong { font-size: 2.2rem; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-owner sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold fs-4 d-flex align-items-center" href="quan-ly-san.php">
            <i class="bi bi-trophy-fill me-2"></i> <?= htmlspecialchars($user['name']) ?>
        </a>
        <div class="d-flex gap-2">
            <a href="quan-ly-san.php" class="btn btn-light">Quản lý sân</a>
            <a href="them-lich-trong.php?san_id=<?= $san['id'] ?>" class="btn btn-success">Lịch trống</a>
            <a href="../logout.php" class="btn btn-outline-light">Đăng xuất</a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="card-main">
        <div class="position-relative">
            <?php
            $imgUrl = 'https://via.placeholder.com/1200x600/333/fff?text=Sân+Bóng+Chất';
            if (!empty($san['hinh_anh'])) {
                $path = $san['hinh_anh'];
                if (strpos($path, 'storage/') === false) $path = 'storage/' . ltrim($path, '/');
                $imgUrl = 'http://127.0.0.1:8000/' . $path;
            }
            ?>
            <img src="<?= $imgUrl ?>" class="san-img" alt="<?= htmlspecialchars($san['ten_san']) ?>">

            <?php if ($tongSo > 0): ?>
            <div class="rating-box">
                <div class="rating-stars"><?= renderStars($trungBinh) ?></div>
                <strong><?= $trungBinh ?></strong>
                <div class="rating-count"><?= $tongSo ?> đánh giá</div>
            </div>
            <?php endif; ?>
        </div>

        <div class="card-body p-5">
            <div class="d-flex justify-content-between align-items-start mb-5">
                <div>
                    <h1 class="display-4 fw-bold mb-2 text-dark"><?= htmlspecialchars($san['ten_san']) ?></h1>
                    <p class="lead text-muted">
                        <i class="bi bi-geo-alt-fill text-danger"></i> <?= htmlspecialchars($san['dia_chi']) ?>
                    </p>
                </div>
                <span class="badge badge-status bg-<?= $san['trang_thai_duyet']==='da_duyet'?'success':($san['trang_thai_duyet']==='tu_choi'?'danger':'warning') ?>">
                    <?= $san['trang_thai_duyet']==='da_duyet' ? 'Đã duyệt' : ($san['trang_thai_duyet']==='tu_choi' ? 'Bị từ chối' : 'Chờ duyệt') ?>
                </span>
            </div>

            <div class="row g-5 mb-5">
                <div class="col-lg-6">
                    <div class="bg-light rounded-4 p-4 border">
                        <h5 class="fw-bold text-primary mb-4"><i class="bi bi-info-circle"></i> Thông tin sân</h5>
                        <p class="mb-3 fs-5"><strong>Loại sân:</strong> <?= htmlspecialchars($san['loai_san'] ?? 'Sân bóng đá') ?></p>
                        <p class="mb-0"><strong>Giá thuê:</strong> 
                            <span class="text-success fs-3 fw-bold">₫<?= number_format($san['gia_thue']) ?></span>
                            <small class="text-muted">/giờ</small>
                        </p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <h5 class="fw-bold text-primary mb-4"><i class="bi bi-file-text"></i> Mô tả</h5>
                    <div class="bg-light rounded-4 p-4 border" style="min-height: 120px;">
                        <?= $san['mo_ta'] ? nl2br(htmlspecialchars($san['mo_ta'])) : '<em class="text-muted">Chưa có mô tả chi tiết.</em>' ?>
                    </div>
                </div>
            </div>

            <!-- ĐÁNH GIÁ KHÁCH HÀNG – ĐẸP NHƯ CUSTOMER: TÊN + SAO CHUNG 1 DÒNG -->
            <div class="mt-5">
                <h3 class="fw-bold mb-4 text-primary">
                    <i class="bi bi-star-fill text-warning"></i> Đánh giá từ khách hàng
                    <span class="badge bg-warning text-dark fs-5 ms-3"><?= $trungBinh ?> ★ (<?= $tongSo ?> lượt)</span>
                </h3>

                <?php if (empty($danhGiaList)): ?>
                    <div class="no-review">
                        <i class="bi bi-chat-square-text"></i>
                        <h4 class="mt-3 text-muted">Chưa có đánh giá nào</h4>
                        <p>Hãy phục vụ thật tốt để nhận đánh giá 5 sao nhé!</p>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($danhGiaList as $dg): 
                            // ĐÃ FIX TÊN KHÁCH – DÙNG ĐÚNG `ten_nguoi_dung` TỪ API
                            $tenKhach = $dg['ten_nguoi_dung'] ?? $dg->ten_nguoi_dung ?? 'Khách';
                            $soSao = $dg['diem_danh_gia'] ?? $dg->diem_danh_gia ?? 0;
                            $noiDung = $dg['noi_dung'] ?? $dg->noi_dung ?? '';
                            $createdAt = $dg['ngay_danh_gia'] ?? $dg->ngay_danh_gia ?? date('Y-m-d H:i:s');
                            $ngayHienThi = date('d/m/Y H:i', strtotime($createdAt));
                            $avatarChar = mb_substr($tenKhach, 0, 1, 'UTF-8');
                        ?>
                            <div class="col-12">
                                <div class="review-card d-flex">
                                    <div class="flex-shrink-0">
                                        <div class="avatar"><?= strtoupper($avatarChar) ?></div>
                                    </div>
                                    <div class="flex-grow-1 ms-4">
                                        <!-- TÊN + SAO CHUNG 1 DÒNG – ĐẸP NHƯ CUSTOMER -->
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold mb-0 text-primary fs-5"><?= htmlspecialchars($tenKhach) ?></h6>
                                            <div>
                                                <?= renderStars($soSao) ?>
                                                <span class="text-warning fw-bold ms-2"><?= number_format($soSao, 1) ?></span>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mb-3">
                                            <i class="bi bi-calendar3"></i> <?= $ngayHienThi ?>
                                        </small>
                                        <?php if ($noiDung): ?>
                                            <p class="mb-0 lh-lg"><?= nl2br(htmlspecialchars($noiDung)) ?></p>
                                        <?php else: ?>
                                            <p class="mb-0 text-muted fst-italic">Chỉ đánh giá sao</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="text-center mt-5 pt-5 border-top">
                <a href="quan-ly-san.php" class="btn btn-secondary btn-lg px-5">Quay lại danh sách</a>
                <a href="sua-san.php?id=<?= $san['id'] ?>" class="btn btn-warning btn-lg px-5 ms-3">Sửa thông tin sân</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>