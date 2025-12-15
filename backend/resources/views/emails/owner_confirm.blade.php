<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Xác nhận đăng ký chủ sân</title>
</head>
<body>
    <h2>Xin chào {{ $user->name }}</h2>
    <p>Bạn vừa đăng ký tài khoản chủ sân. Vui lòng nhấn vào link dưới đây để xác nhận:</p>
    <a href="{{ $confirmUrl }}" 
   style="display:inline-block;
          padding:10px 20px;
          background-color:#28a745;
          color:#fff;
          text-decoration:none;
          border-radius:5px;">
   Xác nhận chủ sân
</a>

</body>
</html>
