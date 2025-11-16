<?php
require_once '../config.php';
requireRole('owner');

$id = $_GET['id'] ?? 0;
$action = $_GET['action'] ?? '';

if(!$id || !in_array($action, ['duyet','tu_choi'])) {
    die('Thiếu dữ liệu hoặc action không hợp lệ');
}

$res = callAPI('POST', '/owner/dat-san/'.$id.'/duyet', ['action'=>$action], $_SESSION['token']);

if(isset($res['message'])){
    echo "<div class='alert alert-success'>{$res['message']}</div>";
    echo "<a href='yeu-cau-dat-san.php'>Quay lại</a>";
} else {
    echo "<div class='alert alert-danger'>Cập nhật thất bại</div>";
    echo "<a href='yeu-cau-dat-san.php'>Quay lại</a>";
}
