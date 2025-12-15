import React, { useEffect, useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";

function ConfirmEmailSuccess() {
  const navigate = useNavigate();
  const location = useLocation();
  const params = new URLSearchParams(location.search);
  const email = params.get("email");
  const [message, setMessage] = useState("Đang xác thực...");

  useEffect(() => {
    fetch(`http://localhost:8000/api/owner/confirm?email=${email}`)
      .then(res => res.json())
      .then(data => setMessage(data.message))
      .catch(() => setMessage("Có lỗi xảy ra!"));

    const timer = setTimeout(() => {
      navigate("/");
    }, 5000);
    return () => clearTimeout(timer);
  }, [email, navigate]);

  return (
    <div className="flex items-center justify-center min-h-screen bg-green-50">
      <div className="bg-white shadow-lg rounded-lg p-8 w-full max-w-md text-center">
        <div className="text-4xl text-green-500 mb-4">✅</div>
        <h2 className="text-xl font-bold mb-2">{message}</h2>
        <p className="text-gray-700 mb-6">
          Tài khoản với địa chỉ <strong>{email}</strong> đã được xác nhận.
        </p>
        <button
          onClick={() => navigate("/")}
          className="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition"
        >
          Quay lại trang chủ
        </button>
        <p className="text-sm text-gray-500 mt-3">
          Bạn sẽ được chuyển về trang chủ sau 5 giây...
        </p>
      </div>
    </div>
  );
}

export default ConfirmEmailSuccess;
