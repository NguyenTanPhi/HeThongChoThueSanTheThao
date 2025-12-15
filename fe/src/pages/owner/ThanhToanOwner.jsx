import { useLocation } from "react-router-dom";

export default function ThanhToan() {
  const location = useLocation();
  const { paymentUrl, tenGoi, gia } = location.state || {};

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 px-4">
      <div className="bg-white rounded-xl shadow-lg p-8 max-w-md w-full text-center">
        <div className="text-blue-600 text-5xl mb-4">💳</div>
        <h1 className="text-2xl font-bold mb-4 text-blue-700">
          Thanh toán gói dịch vụ
        </h1>

        <div className="text-left text-gray-700 mb-6">
          <p className="mb-2">
            <strong>Gói dịch vụ:</strong> {tenGoi || "Không xác định"}
          </p>
          <p className="mb-2">
            <strong>Giá:</strong>{" "}
            {gia ? `${Number(gia).toLocaleString("vi-VN")} ₫` : "0 ₫"}
          </p>
        </div>

        <a
          href={paymentUrl}
          className="btn btn-primary btn-lg w-full shadow-md"
        >
          Thanh toán ngay
        </a>

        <p className="text-sm text-gray-400 mt-4">
          Bạn sẽ được chuyển đến cổng thanh toán VNPay để hoàn tất giao dịch.
        </p>
      </div>
    </div>
  );
}
