// src/pages/owner/VnpayReturn.jsx
import { useEffect, useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { axiosPrivate } from "../../api/instance";


export default function VnpayReturnOwner() {
  const location = useLocation();
  const [status, setStatus] = useState(null);
  const navigate = useNavigate();
  const [info, setInfo] = useState({});
  const [message, setMessage] = useState("");

  useEffect(() => {
  const params = new URLSearchParams(location.search);
  const responseCode = params.get("vnp_ResponseCode");
  const orderCode = params.get("vnp_TxnRef");
  const goiId = params.get("goiId");
  const amount = parseInt(params.get("vnp_Amount") || "0") / 100;

  const isSuccess = responseCode === "00";

  setStatus(isSuccess ? "success" : "fail");

  setInfo({
    orderCode,
    amount,
    transId: params.get("vnp_TransactionNo"),
    bankCode: params.get("vnp_BankCode"),
    payDate: params.get("vnp_PayDate"),
  });

  if (!isSuccess) {
    setMessage("Thanh toán thất bại!");
    return;
  }

  // ✔ Thanh toán thành công
  setMessage("Đang xử lý kích hoạt gói dịch vụ...");

  if (orderCode && goiId) {
    axiosPrivate.post(`/owner/check-thanh-toan/${orderCode}`, {
      goi_dich_vu_id: goiId,
      amount,
      payment_method: "vnpay",
      vnp_transaction_no: params.get("vnp_TransactionNo"),
    })
      .then(() => {
        setMessage("Gói dịch vụ đã được kích hoạt.");
      })
      .catch(() => {
        setMessage("Thanh toán thành công nhưng lưu giao dịch thất bại.");
      });
  }
}, [location.search]);


  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50">
      <div className="bg-white p-8 rounded-xl shadow-lg max-w-md w-full text-center">
        {status === "success" ? (
          <>
            <div className="text-green-600 text-5xl mb-4">✔</div>
            <h2 className="text-2xl font-bold mb-4 text-green-600">
              Thanh toán thành công!
            </h2>
            <p>{message}</p>
          </>
        ) : (
          <>
            <div className="text-red-600 text-5xl mb-4">✖</div>
            <h2 className="text-2xl font-bold mb-4 text-red-600">
              Thanh toán thất bại!
            </h2>
            <p>{message}</p>
          </>
        )}
        <hr className="my-4" />
        <p><b>Mã đơn:</b> {info.orderCode}</p>
        <p><b>Số tiền:</b> {info.amount}₫</p>
        {info.transId && <p><b>Mã giao dịch:</b> {info.transId}</p>}
        {info.bankCode && <p><b>Ngân hàng:</b> {info.bankCode}</p>}
        {info.payDate && <p><b>Thời gian:</b> {info.payDate}</p>}
        <hr className="my-4" />
        <button
      className="btn btn-primary mt-4"
      onClick={() => navigate("/owner/dashboard?tab=goi-dich-vu")}
    >
      Quay về quản lý gói dịch vụ
    </button>

      </div>
    </div>
  );
}
