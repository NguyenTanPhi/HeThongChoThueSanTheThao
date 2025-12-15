import React, { useEffect, useState } from "react";
import { axiosPrivate } from "../../api/instance";
import { useNavigate } from "react-router-dom";
import { toast, ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

export default function LichSuDat() {
  const [lichSu, setLichSu] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedBooking, setSelectedBooking] = useState(null);
  const [rating, setRating] = useState(0);
  const [comment, setComment] = useState("");
  const navigate = useNavigate();

  const statusMap = {
    da_thanh_toan: { text: "Đã thanh toán", bg: "bg-green-100", textColor: "text-green-800" },
    chua_thanh_toan: { text: "Chưa thanh toán", bg: "bg-yellow-100", textColor: "text-yellow-800" },
    da_huy: { text: "Đã hủy", bg: "bg-red-100", textColor: "text-red-800" },
    cho_xac_nhan: { text: "Chờ xác nhận", bg: "bg-orange-100", textColor: "text-orange-800" },
  };
  const completedStatus = {
  text: "Đã hoàn thành",
  bg: "bg-blue-100",
  textColor: "text-blue-800",
};
  useEffect(() => {
    const fetchData = async () => {
      const token = localStorage.getItem("token");
      if (!token) {
        toast.error("❌ Bạn cần đăng nhập để truy cập");
        navigate("/");
        return;
      }

      try {
        const res = await axiosPrivate.get("/customer/dat-san");
        const data = res?.data?.data ?? res?.data ?? [];
        setLichSu(Array.isArray(data) ? data : []);
      } catch {
        toast.error("❌ Bạn cần đăng nhập để truy cập");
        navigate("/");
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, [navigate]);

  const submitReview = async () => {
    try {
      await axiosPrivate.post("/danh-gia", {
        san_id: selectedBooking.san?.id,
        diem_danh_gia: rating,
        noi_dung: comment,
      });
      toast.success("🎉 Đánh giá thành công!", { position: "top-right" });

      setLichSu((prev) =>
        prev.map((item) =>
          item.id === selectedBooking.id ? { ...item, da_danh_gia: true } : item
        )
      );
      setSelectedBooking(null);
      setRating(0);
      setComment("");
    } catch (err) {
      const msg = err.response?.data?.message || "Lỗi khi gửi đánh giá!";
      toast.error(`❌ ${msg}`, { position: "top-right" });
    }
  };

  if (loading) {
    return <div className="p-10 text-center text-gray-500 text-lg">Đang tải...</div>;
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-green-50 to-white px-4 py-12">
      <ToastContainer />
      <h1 className="text-4xl font-extrabold mb-10 text-center text-green-700 drop-shadow-md">
        📖 Lịch sử đặt sân của bạn
      </h1>

      {lichSu.length > 0 ? (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {lichSu.map((item) => {
            const daDanhGia =
              item?.da_danh_gia === true ||
              (Array.isArray(item?.danh_gia) && item.danh_gia.length > 0);

            const status = item.da_hoan_thanh
  ? completedStatus
  : statusMap[item.trang_thai] || {
      text: item.trang_thai,
      bg: "bg-gray-100",
      textColor: "text-gray-800",
    };


            return (
              <div
                key={item.id}
                className="bg-white rounded-2xl shadow-2xl p-6 flex flex-col justify-between border border-gray-200 hover:shadow-green-400 transition"
              >
                <div>
                  <h2 className="text-xl font-semibold mb-2 text-gray-800">{item.san?.ten_san}</h2>
                  <p className="text-gray-600 mb-1"><b>Ngày:</b> {item.ngay_dat}</p>
                  <p className="text-gray-600 mb-1"><b>Giờ:</b> {item.gio_bat_dau} - {item.gio_ket_thuc}</p>
                  <p className="text-gray-600 mb-1"><b>Giá:</b> {Number(item.tong_gia || 0).toLocaleString("vi-VN")}đ</p>
                  <p className="text-gray-600 mb-3">
                    <b>Trạng thái:</b>{" "}
                    <span className={`font-semibold px-2 py-1 rounded-full text-sm ${status.bg} ${status.textColor}`}>
                      {status.text}
                    </span>
                  </p>
                </div>

                {!daDanhGia && (
                  <button
                    className="btn btn-warning btn-sm mt-2 hover:bg-yellow-500 transition"
                    onClick={() => setSelectedBooking(item)}
                  >
                    ⭐ Đánh giá
                  </button>
                )}
              </div>
            );
          })}
        </div>
      ) : (
        <p className="text-center text-gray-500 text-lg">Chưa có lịch sử đặt sân.</p>
      )}

      <div className="text-center mt-10">
        <button
          className="btn btn-primary px-6 py-2 rounded-xl shadow-md hover:bg-green-600 transition font-medium"
          onClick={() => navigate("/")}
        >
          ⬅ Quay về trang chủ
        </button>
      </div>

      {/* Modal đánh giá */}
      {selectedBooking && (
        <div className="fixed inset-0 flex items-center justify-center z-50 px-4">
          <div
            className="absolute inset-0 bg-black bg-opacity-50"
            onClick={() => setSelectedBooking(null)}
          ></div>

          <div className="bg-white rounded-2xl shadow-2xl p-6 z-10 w-full max-w-md border border-gray-200">
            <h2 className="text-2xl font-bold mb-4 text-green-700">
              Đánh giá sân {selectedBooking.san?.ten_san}
            </h2>

            <div className="flex gap-2 mb-4 justify-center text-3xl">
              {[1, 2, 3, 4, 5].map((star) => (
                <button
                  key={star}
                  type="button"
                  className={`transition-transform ${rating >= star ? "scale-110 text-yellow-400" : "text-gray-300"}`}
                  onClick={() => setRating(star)}
                >
                  ★
                </button>
              ))}
            </div>

            <textarea
              className="textarea textarea-bordered w-full mb-4 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
              placeholder="Nhập nội dung đánh giá (tối thiểu 10 ký tự)..."
              value={comment}
              onChange={(e) => setComment(e.target.value)}
            />

            <div className="flex justify-end gap-3">
              <button
                className="btn btn-gray px-4 py-2 rounded-lg hover:bg-gray-200 transition"
                onClick={() => setSelectedBooking(null)}
              >
                Hủy
              </button>
              <button
                className="btn btn-success px-4 py-2 rounded-lg shadow-md hover:bg-green-700 transition"
                disabled={rating === 0 || comment.trim().length < 10}
                onClick={submitReview}
              >
                Gửi đánh giá
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
