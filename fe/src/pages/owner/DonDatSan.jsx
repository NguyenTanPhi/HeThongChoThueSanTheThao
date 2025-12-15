// src/pages/owner/DonDatSan.jsx
import React, { useEffect, useState } from "react";
import { axiosPrivate } from "../../api/instance";

export default function DonDatSan() {
  const [donList, setDonList] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const res = await axiosPrivate.get("/owner/lich-su-dat");
        setDonList(res.data || []);
      } catch (err) {
        console.error("Lỗi khi lấy đơn đặt sân:", err);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, []);

  const formatDate = (dateStr) => {
    return new Date(dateStr).toLocaleString("vi-VN", {
      weekday: "short",
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  };

  const formatNgayDat = (ngay) => {
    const date = new Date(ngay);
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    if (date.toDateString() === today.toDateString()) return "Hôm nay";
    if (date.toDateString() === tomorrow.toDateString()) return "Ngày mai";

    return date.toLocaleDateString("vi-VN", {
      weekday: "long",
      day: "numeric",
      month: "short",
      year: "numeric",
    });
  };

  const getTrangThai = (don) => {
    const now = new Date();
    const ketThuc = new Date(`${don.ngay_dat} ${don.gio_ket_thuc}`);

    if (now > ketThuc) {
      return { text: "Đã hoàn thành", color: "bg-green-100 text-green-800" };
    }
    if (don.trang_thai === "da_thanh_toan") {
      return { text: "Đã thanh toán", color: "bg-blue-100 text-blue-800" };
    }
    if (don.trang_thai === "cho_thanh_toan") {
      return { text: "Chờ thanh toán", color: "bg-yellow-100 text-yellow-800" };
    }
    return { text: "Chưa xác định", color: "bg-gray-100 text-gray-800" };
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-96">
        <span className="loading loading-spinner loading-lg text-primary"></span>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 py-8 px-4">
      <div className="max-w-5xl mx-auto">
        <h1 className="text-4xl font-bold text-center mb-2 text-gray-800">
          Các đơn đặt sân
        </h1>
        <p className="text-center text-gray-600 mb-8">
          Quản lý và theo dõi tất cả lịch đặt sân
        </p>

        {donList.length === 0 ? (
          <div className="text-center py-20">
            <div className="text-6xl mb-4">🎾</div>
            <p className="text-xl text-gray-500">
              Chưa có đơn đặt sân nào. Khi có khách đặt, đơn sẽ hiện ở đây.
            </p>
          </div>
        ) : (
          <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-1">
            {donList.map((don) => {
              const status = getTrangThai(don);

              return (
                <div
                  key={don.id}
                  className="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100"
                >
                  <div className="bg-gradient-to-r from-blue-600 to-purple-600 p-4 text-white">
                    <div className="flex justify-between items-center">
                      <h3 className="text-xl font-bold">{don.san?.ten_san || "Sân không xác định"}</h3>
                      <span className={`px-4 py-1 rounded-full text-sm font-bold ${status.color}`}>
                        {status.text}
                      </span>
                    </div>
                  </div>

                  <div className="p-6">
                    <div className="flex items-center gap-3 mb-4">
                      <div className="w-12 h-12 bg-gradient-to-br from-pink-500 to-orange-400 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md">
                        {don.user?.name?.charAt(0).toUpperCase() || "?"}
                      </div>
                      <div>
                        <p className="font-semibold text-lg">{don.user?.name || "Khách vãng lai"}</p>
                        <p className="text-sm text-gray-500">SĐT: {don.user?.phone || "Chưa có"}</p>
                      </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4 text-gray-700">
                      <div>
                        <p className="text-sm text-gray-500">Ngày chơi</p>
                        <p className="font-bold text-lg">{formatNgayDat(don.ngay_dat)}</p>
                      </div>
                      <div>
                        <p className="text-sm text-gray-500">Giờ chơi</p>
                        <p className="font-bold text-lg text-purple-600">
                          {don.gio_bat_dau} - {don.gio_ket_thuc}
                        </p>
                      </div>
                    </div>

                    <div className="mt-4 pt-4 border-t border-gray-200">
                      <div className="flex justify-between items-center">
                        <div>
                          <p className="text-sm text-gray-500">Tổng tiền</p>
                          <p className="text-2xl font-bold text-green-600">
                            {Number(don.tong_gia || don.gia).toLocaleString("vi-VN")}đ
                          </p>
                        </div>
                        <div className="text-right">
                          <p className="text-sm text-gray-500">Đặt lúc</p>
                          <p className="text-sm font-medium">{formatDate(don.created_at)}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </div>
    </div>
  );
}
