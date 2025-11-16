<?php
session_start();

// URL Backend Laravel
define('API_URL', 'http://localhost:8000/api');

// Hàm gọi API với token (GET, POST, PUT, DELETE) – HỖ TRỢ FILE UPLOAD
function callAPI($method, $endpoint, $data = null, $token = null)
{
    $url = rtrim(API_URL, '/') . $endpoint;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));

    $headers = [
        'Accept: application/json',
    ];

    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }

    // PHÁT HIỆN CÓ FILE KHÔNG?
    $hasFile = false;
    if (is_array($data)) {
        foreach ($data as $value) {
            if ($value instanceof CURLFile) {
                $hasFile = true;
                break;
            }
        }
    }

    if ($hasFile) {
        // GỬI DẠNG MULTIPART/FORM-DATA
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        // Không set Content-Type → cURL tự thêm boundary
    } elseif ($data !== null) {
        // GỬI DẠNG JSON
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        $headers[] = 'Content-Type: application/json';
    }

    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        return ['error' => true, 'message' => 'cURL Error: ' . $error];
    }

    // XỬ LÝ LỖI HTTP
    if ($httpCode >= 400) {
        $decoded = json_decode($response, true);
        $message = $decoded['message'] ?? ($response ?: 'Lỗi API');
        return ['error' => true, 'message' => $message, 'code' => $httpCode];
    }

    // Decode JSON nếu được
    $decoded = json_decode($response, true);
    return $decoded !== null ? $decoded : $response;
}

// Kiểm tra đăng nhập
function isLogin()
{
    return isset($_SESSION['user']) && isset($_SESSION['token']);
}

// Lấy thông tin user hiện tại
function getUser()
{
    return $_SESSION['user'] ?? null;
}

// Kiểm tra quyền truy cập
function requireRole($roles = [])
{
    if (!isLogin()) {
        header('Location: ../login.php');
        exit;
    }

    $user = getUser();
    $currentRole = $user['role'] ?? 'customer';

    if (!in_array($currentRole, (array)$roles)) {
        // Redirect theo role
        if ($currentRole === 'admin') {
            header('Location: ../admin/dashboard.php');
        } elseif ($currentRole === 'owner') {
            header('Location: ../owner/quan-ly-san.php');
        } else {
            header('Location: ../customer/home.php');
        }
        echo "<h3 style='color:red;text-align:center;margin-top:50px'>
                Bạn không có quyền truy cập trang này!</h3>";
        exit;
    }
}

// Đăng xuất
function logout()
{
    if (isLogin()) {
        $token = $_SESSION['token'];
        callAPI('POST', '/logout', null, $token);
    }
    session_destroy();
    header('Location: ../login.php');
    exit;
}
