import { useEffect, useState } from "react";
import { useLocation, Link, useNavigate } from "react-router-dom";
import { axiosPrivate } from "../../api/instance";

export default function ZaloGoiReturn() {
  const location = useLocation();
  const navigate = useNavigate();
  const [status, setStatus] = useState("processing");
  const [message, setMessage] = useState("Đang xử lý kết quả thanh toán...");
  const [info, setInfo] = useState({});

  useEffect(() => {
    const params = new URLSearchParams(location.search);
    const statusParam = params.get("status");
    const isSuccess = statusParam === "1";
    const goiId = params.get("goiId");
    const orderCode = params.get("apptransid");
    const amount = params.get("amount");
    if (isSuccess) {
      setStatus("success");
      setMessage("Thanh toán ZaloPay thành công!");
      setInfo({
        orderCode: orderCode || "Không có",
        amount: amount ? Number(amount).toLocaleString("vi-VN") : "0",
      });

      // Call backend to finalize even if goiId is not present;
      // backend will try to pull cache meta by orderCode
      if (orderCode) {
        const payload = {
          ...(goiId ? { goi_dich_vu_id: goiId } : {}),
          amount,
          payment_method: "zalopay",
          zalo_transaction_no: orderCode,
        };
        axiosPrivate.post(`/owner/check-thanh-toan/${orderCode}`, payload)
          .then(() => {
            setMessage("Gói dịch vụ đã được kích hoạt.");
          })
          .catch(() => {
            setMessage("Thanh toán thành công nhưng lưu giao dịch thất bại.");
          });
      }

      // Có thể tự động redirect sau 4 giây
      setTimeout(() => {
        navigate("/owner/dashboard?tab=goi-dich-vu");
      }, 4000);
    } else {
      setStatus("fail");
      setMessage("Thanh toán thất bại hoặc bị hủy.");
    }
  }, [location, navigate]);

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 py-8">
      <div className="bg-white p-8 rounded-xl shadow-lg max-w-md w-full text-center">
        {status === "processing" ? (
          <div className="py-12">
            <span className="loading loading-spinner loading-lg text-primary mb-4"></span>
            <p className="text-lg">{message}</p>
          </div>
        ) : status === "success" ? (
          <>
            <div className="text-green-600 text-6xl mb-6">✓</div>
            <h2 className="text-2xl font-bold text-green-600 mb-4">
              Thanh toán thành công!
            </h2>
            <p className="text-lg mb-6">{message}</p>

            <div className="bg-gray-50 p-4 rounded-lg mb-6">
              <p><strong>Mã đơn:</strong> {info.orderCode}</p>
              <p><strong>Số tiền:</strong> {info.amount} ₫</p>
            </div>

            <p className="text-sm text-gray-500 mb-6">
              Bạn sẽ được chuyển hướng về trang gói dịch vụ trong vài giây...
            </p>

            <Link to="/owner/dashboard?tab=goi-dich-vu" className="btn btn-primary">
              Về trang gói dịch vụ
            </Link>
          </>
        ) : (
          <>
            <div className="text-red-600 text-6xl mb-6">✗</div>
            <h2 className="text-2xl font-bold text-red-600 mb-4">
              Thanh toán không thành công
            </h2>
            <p className="text-lg mb-8">{message}</p>

            <Link to="/owner/goi-dich-vu" className="btn btn-outline btn-error">
              Thử lại
            </Link>
          </>
        )}
      </div>
    </div>
  );
}