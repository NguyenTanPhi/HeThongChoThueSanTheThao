<?php
require_once '../config.php';
requireRole('admin');
$user = getUser();

// Xử lý khóa/mở khóa
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['action'] === 'lock' ? 'locked' : 'active';

    $res = callAPI('PUT', "/admin/user/{$id}/status", ['status' => $status], $_SESSION['token']);

    if ($res && empty($res['error'])) {
        $msg = $res['message'] ?? ($status === 'locked' ? 'Đã khóa thành công!' : 'Đã mở khóa thành công!');
    } else {
        $msg = $res['error'] ?? $res['message'] ?? 'Thao tác thất bại!';
    }
}

// Lấy danh sách người dùng
$page = max(1, (int)($_GET['page'] ?? 1));
$search = trim($_GET['q'] ?? '');

$params = ['page' => $page, 'limit' => 15];
if ($search !== '') $params['search'] = $search;

$response = callAPI('GET', '/admin/users', $params, $_SESSION['token']);

$users = $response['data'] ?? [];
$total = $response['total'] ?? count($users);
$totalPages = $response['last_page'] ?? 1;

// === HÀM CHUYỂN ROLE SANG TIẾNG VIỆT – TƯƠNG THÍCH PHP CŨ ===
function getRoleName($role) {
    switch ($role) {
        case 'admin':    return 'QUẢN TRỊ VIÊN';
        case 'owner':    return 'CHỦ SÂN';
        case 'customer': return 'KHÁCH HÀNG';
        default:         return 'KHÁCH HÀNG';
    }
}
function getRoleColor($role) {
    switch ($role) {
        case 'admin':    return 'bg-danger';
        case 'owner':    return 'bg-warning text-dark';
        case 'customer': return 'bg-success';
        default:         return 'bg-secondary';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; font-family: 'Segoe UI', sans-serif; padding: 20px 0; }
        .container { max-width: 1250px; }
        .header { background: linear-gradient(45deg, #1e3799, #4a69bd); color: white; padding: 40px; border-radius: 20px 20px 0 0; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .header h1 { margin: 0; font-weight: 900; font-size: 2.8rem; text-shadow: 0 4px 10px rgba(0,0,0,0.4); }
        .card { background: white; border-radius: 0 0 20px 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.3); }
        .table th { background: linear-gradient(135deg, #5f27cd, #6c5ce7); color: white; font-weight: 600; }

        .badge { padding: 10px 20px; border-radius: 50px; font-weight: bold; font-size: 0.95rem; }

        /* NÚT KHÓA SIÊU ĐẸP */
        .btn-action {
            width: 56px; height: 56px; border-radius: 50%; font-size: 1.6rem;
            display: inline-flex; align-items: center; justify-content: center;
            box-shadow: 0 6px 15px rgba(0,0,0,0.2); transition: all 0.3s ease;
            text-decoration: none !important; color: white;
        }
        .btn-lock   { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        .btn-unlock { background: linear-gradient(135deg, #27ae60, #2ecc71); }
        .btn-action:hover { transform: translateY(-4px); box-shadow: 0 12px 25px rgba(0,0,0,0.3); }

        .logout-btn { position: fixed; top: 20px; right: 20px; background: #e74c3c; color: white; padding: 15px 40px; border-radius: 50px; font-weight: bold; text-decoration: none; box-shadow: 0 8px 20px rgba(231,76,60,0.6); z-index: 9999; }
        .logout-btn:hover { background: #c0392b; }
    </style>
</head>
<body>

<a href="../logout.php" class="logout-btn">ĐĂNG XUẤT</a>

<div class="container">
    <div class="header">
        <h1>QUẢN LÝ NGƯỜI DÙNG</h1>
        <p>Xin chào <strong><?= htmlspecialchars($user['name'] ?? '') ?></strong> – Tổng: <strong><?= $total ?></strong> tài khoản</p>
    </div>

    <div class="card">
        <div class="card-body p-4">

            <?php if ($msg): ?>
                <div class="alert <?= strpos($msg,'thành công') !== false ? 'alert-success' : 'alert-danger' ?> text-center fw-bold fs-5">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <form method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-8">
                        <input type="text" name="q" class="form-control form-control-lg" placeholder="Tìm tên, email, số điện thoại..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 h-100">Tìm kiếm</button>
                    </div>
                    <div class="col-md-2">
                        <a href="quan-ly-user.php" class="btn btn-secondary w-100 h-100">Xóa lọc</a>
                    </div>
                </div>
            </form>

            <?php if (empty($users)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users-slash fa-6x text-muted mb-4"></i>
                    <h4 class="text-muted">Không tìm thấy người dùng nào</h4>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>SĐT</th>
                                <th>Vai trò</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): 
                                $isAdmin = ($u['role'] ?? '') === 'admin';
                                $isLocked = isset($u['status']) ? ($u['status'] === 'locked') : false;
                            ?>
                                <tr <?= $isLocked ? 'style="opacity:0.65; background:#fff5f5"' : '' ?>>
                                    <td><strong>#<?= $u['id'] ?></strong></td>
                                    <td class="fw-bold"><?= htmlspecialchars($u['name'] ?? 'Chưa đặt tên') ?></td>
                                    <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($u['phone'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge <?= getRoleColor($u['role'] ?? 'customer') ?> px-4 py-2">
                                            <?= getRoleName($u['role'] ?? 'customer') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $isLocked 
                                            ? '<span class="badge bg-danger fs-6">ĐÃ KHÓA</span>'
                                            : '<span class="badge bg-success fs-6">HOẠT ĐỘNG</span>'
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!$isAdmin): ?>
                                            <?php if ($isLocked): ?>
                                                <a href="?action=unlock&id=<?= $u['id'] ?>&page=<?= $page ?>&q=<?= urlencode($search) ?>" 
                                                   class="btn-action btn-unlock" title="Mở khóa">
                                                    Mở khóa
                                                </a>
                                            <?php else: ?>
                                                <a href="?action=lock&id=<?= $u['id'] ?>&page=<?= $page ?>&q=<?= urlencode($search) ?>" 
                                                   class="btn-action btn-lock" title="Khóa tài khoản"
                                                   onclick="return confirm('Bạn có chắc chắn muốn KHÓA tài khoản này?')">
                                                    Khóa
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted fw-bold">ADMIN</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&q=<?= urlencode($search) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>

            <div class="text-center mt-5">
                <a href="dashboard.php" class="btn btn-light btn-lg px-5">Quay lại</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>