// src/pages/customer/ThanhToan.jsx
import { useEffect, useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { axiosPrivate } from "../../api/instance";

export default function ThanhToan() {
  const location = useLocation();
  const navigate = useNavigate();
  const [lich, setLich] = useState(null);
  const [lichId, setLichId] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [paymentMethod, setPaymentMethod] = useState("vnpay"); // mặc định vnpay

  // lấy thông tin lịch từ query string để hiển thị
  useEffect(() => {
    const params = new URLSearchParams(location.search);
    setLichId(params.get("lich_id"));
    setLich({
      ngay: params.get("ngay"),
      gio_bat_dau: params.get("gio_bat_dau"),
      gio_ket_thuc: params.get("gio_ket_thuc"),
      gia: params.get("gia"),
    });
  }, [location.search]);

  const handleThanhToan = async () => {
    const token = localStorage.getItem("token");
    if (!token) {
      setError("❌ Bạn cần đăng nhập trước khi đặt sân");
      // sau 2 giây tự động quay về Home
      setTimeout(() => navigate("/login"), 1000);
      return;
    }

    setLoading(true);
    setError("");
    try {
      const res = await axiosPrivate.post("/customer/dat-san-thanh-toan", {
        lich_id: lichId,
        payment_method: paymentMethod, // gửi đúng method
      });

      const { payment_url } = res.data;
      if (!payment_url) throw new Error("Không tạo được link thanh toán");

      window.location.href = payment_url;
    } catch (err) {
      setError(err.response?.data?.message || err.message);
    } finally {
      setLoading(false);
    }
  };

  if (!lich) return <div className="p-10 text-center">Đang tải thông tin...</div>;

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50">
      <div className="bg-white p-8 rounded-xl shadow-lg max-w-md w-full text-center">
        <h1 className="text-2xl font-bold mb-6">Thanh toán đặt sân</h1>
        <p className="mb-4">
          <b>Ngày:</b> {lich.ngay}<br />
          <b>Giờ:</b> {lich.gio_bat_dau} - {lich.gio_ket_thuc}<br />
          <b>Giá:</b> {Number(lich.gia).toLocaleString("vi-VN")}đ
        </p>

        <div className="mb-4 text-left">
          <label className="font-semibold mb-2 block">Chọn phương thức thanh toán:</label>
          <div className="flex gap-4">
            <label>
              <input
                type="radio"
                name="payment_method"
                value="vnpay"
                checked={paymentMethod === "vnpay"}
                onChange={() => setPaymentMethod("vnpay")}
              />{" "}
              VNPay
            </label>
            <label>
              <input
                type="radio"
                name="payment_method"
                value="zalo"
                checked={paymentMethod === "zalo"}
                onChange={() => setPaymentMethod("zalo")}
              />{" "}
              ZaloPay
            </label>
          </div>
        </div>

        {error && <div className="alert alert-error mb-4">{error}</div>}

        <button
          className="btn btn-success w-full"
          onClick={handleThanhToan}
          disabled={loading}
        >
          {loading ? "Đang xử lý..." : "Thanh toán ngay"}
        </button>
      </div>
    </div>
  );
}
