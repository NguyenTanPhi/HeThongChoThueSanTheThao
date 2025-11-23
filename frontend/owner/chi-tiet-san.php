<?php
require_once '../config.php';
requireRole('owner');

$id = $_GET['id'] ?? 0;
if (!$id) die('<div class="alert alert-danger text-center p-5">Thiếu ID sân!</div>');

$san = callAPI('GET', '/san/' . $id, null, $_SESSION['token']);
if (!$san || !isset($san['id'])) die('<div class="alert alert-danger text-center p-5">Không tìm thấy sân!</div>');

// LẤY LỊCH TRỐNG
$lichTrongRes = callAPI('GET', '/san/' . $id . '/lich-trong', null, $_SESSION['token']);
$lichTrong = [];
if ($lichTrongRes && is_array($lichTrongRes)) {
    $lichTrong = $lichTrongRes;
}


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
<!-- LỊCH TRỐNG (GIỮ NGUYÊN NHƯ CŨ) -->
    <h3 class="mb-4 text-center">Lịch trống hiện có</h3>
    <?php if (!empty($lichTrong)): ?>
        <div class="row g-4">
            <?php foreach ($lichTrong as $lich): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="slot-card p-4 bg-white rounded-4 shadow-sm border text-center h-100">
    <div class="slot-date fw-bold fs-5 mb-2 text-primary">
        <?= date('d/m', strtotime($lich['ngay'])) ?>
        <small class="d-block text-muted"><?= ['Chủ nhật','Thứ 2','Thứ 3','Thứ 4','Thứ 5','Thứ 6','Thứ 7'][date('w', strtotime($lich['ngay']))] ?></small>
    </div>
    <div class="slot-time text-dark fw-bold fs-4 mb-2">
        <?= substr($lich['gio_bat_dau'],0,5) ?> - <?= substr($lich['gio_ket_thuc'],0,5) ?>
    </div>
    <div class="slot-price text-success fw-bold fs-5 mb-3">
        <?= number_format($lich['gia'] ?? $san['gia_thue']) ?>₫
    </div>
    <button class="btn btn-danger w-100 fw-bold btn-xoa-lich" 
        data-san="<?= $san['id'] ?>" 
        data-lich="<?= $lich['id'] ?>" 
        data-bs-toggle="modal" 
        data-bs-target="#confirmDeleteModal">
    <i class="bi bi-trash"></i> Xóa
</button>


</div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5 bg-white rounded-4 shadow">
            <h5 class="text-muted">Hiện chưa có khung giờ trống</h5>
        </div>
    <?php endif; ?>
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
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i> Xác nhận xóa</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center p-4">
        <p class="fs-5 mb-3">Bạn có chắc chắn muốn xóa lịch trống này?</p>
        <p class="text-muted">Hành động này không thể hoàn tác.</p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">Xóa</button>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg text-center">
      <div class="modal-header bg-success text-white justify-content-center">
        <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2"></i> Thành công</h5>
      </div>
      <div class="modal-body p-4">
        <p class="fs-5 mb-0">Đã xóa lịch trống thành công!</p>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-success px-4" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  let sanIdToDelete = null, lichIdToDelete = null;

  document.querySelectorAll('.btn-xoa-lich').forEach(btn => {
    btn.addEventListener('click', () => {
      sanIdToDelete = btn.dataset.san;
      lichIdToDelete = btn.dataset.lich;
    });
  });

  const confirmBtn = document.getElementById('confirmDeleteBtn');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', async () => {
      if (!sanIdToDelete || !lichIdToDelete) return;
      try {
        const res = await fetch(`http://127.0.0.1:8000/api/owner/san/${sanIdToDelete}/lich-trong/${lichIdToDelete}`, {
          method: 'DELETE',
          headers: {
            'Authorization': 'Bearer <?= $_SESSION['token'] ?>',
            'Accept': 'application/json'
          }
        });
        if (res.ok) {
          document.querySelector(`.btn-xoa-lich[data-lich="${lichIdToDelete}"]`).closest('.slot-card').remove();
          bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal')).hide();
          new bootstrap.Modal(document.getElementById('successModal')).show();
        } else {
          new bootstrap.Modal(document.getElementById('successModal')).show();
        document.querySelector('#successModal .modal-body').innerHTML = '<p class="fs-5 mb-0 text-danger">Xóa thất bại!</p>';
        }
      } catch (e) {
        new bootstrap.Modal(document.getElementById('successModal')).show();
  document.querySelector('#successModal .modal-body').innerHTML = '<p class="fs-5 mb-0 text-danger">Có lỗi xảy ra khi gọi API.</p>';
      }
    });
  }

  function showToast(message, type) {
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-bg-${type} border-0`;
    toastEl.role = 'alert';
    toastEl.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${message}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>`;
    document.body.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
  }
});
</script>
</body>
</html>
