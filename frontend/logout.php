<?php
require_once 'config.php';

// BƯỚC 1: Gọi API logout để xóa token ở backend
if (isLogin()) {
    $token = $_SESSION['token'];
    $response = callAPI('POST', '/logout', null, $token);
    // Không cần kiểm tra response, cứ xóa là được
}

// BƯỚC 2: Xóa hết session PHP
session_unset();
session_destroy();

// BƯỚC 3: Chuyển về trang login
header('Location: login.php');
exit;
?>