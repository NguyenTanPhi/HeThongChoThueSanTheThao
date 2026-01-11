// src/pages/owner/ThongBaoOwner.jsx
import React, { useEffect, useState } from "react";
import { axiosPrivate } from "../../api/instance";

export default function ThongBaoOwner() {
  const [notifications, setNotifications] = useState([]);
  const [loading, setLoading] = useState(true);
  const [visibleCount, setVisibleCount] = useState(3);


  useEffect(() => {
    const fetchNotifications = async () => {
      try {
        const res = await axiosPrivate.get("/owner/notifications");

        // Xử lý dữ liệu trả về
        let data = res.data;
        if (Array.isArray(data)) setNotifications(data);
        else if (Array.isArray(data?.data)) setNotifications(data.data);
        else if (Array.isArray(data?.notifications)) setNotifications(data.notifications);
        else setNotifications([]);
      } catch (err) {
        console.error("Lỗi tải thông báo:", err);
        setNotifications([]);
      } finally {
        setLoading(false);
      }
    };
    fetchNotifications();
  }, []);

  const markRead = async (id) => {
    try {
      await axiosPrivate.post(`/owner/notifications/${id}/read`);
      setNotifications((prev) =>
        prev.map((n) => (n.id === id ? { ...n, trang_thai: "read" } : n))
      );
    } catch (err) {
      console.error("Lỗi đánh dấu đã đọc:", err);
    }
  };

  if (loading)
    return (
      <div className="flex justify-center items-center py-10">
        <span className="text-gray-500 animate-pulse">Đang tải thông báo...</span>
      </div>
    );

  return (
    <div className="max-w-4xl mx-auto p-6 bg-white rounded-2xl shadow-lg">
      <h2 className="text-3xl font-bold mb-6 text-primary text-center">📢 Thông báo</h2>

      {notifications.length === 0 ? (
        <div className="text-center py-10 text-gray-400">
          Không có thông báo nào
        </div>
      ) : (
        <ul className="space-y-4">
          {notifications.slice(0, visibleCount).map((tb) => (
            <li
              key={tb.id}
              className={`p-4 rounded-xl border transition-shadow flex justify-between items-start
                ${tb.trang_thai === "read" ? "bg-gray-100 border-gray-200" : "bg-yellow-50 border-yellow-200 hover:shadow-md"}
              `}
            >
              <div className="flex-1">
                <p className="font-semibold text-gray-800">{tb.noi_dung}</p>
                {tb.ly_do && (
                  <p className="text-sm text-gray-500 mt-1">Lý do: {tb.ly_do}</p>
                )}
                <small className="text-gray-400 mt-1 block">
                  {tb.created_at
                    ? new Date(tb.created_at).toLocaleString("vi-VN")
                    : "Không rõ thời gian"}
                </small>
              </div>

              {tb.trang_thai !== "read" && (
                <button
                  className="btn btn-sm btn-primary ml-4 flex-shrink-0"
                  onClick={() => markRead(tb.id)}
                >
                  Đánh dấu đã đọc
                </button>
              )}
            </li>
          ))}
        </ul>
      )}
      {/* Xem thêm / Thu gọn */}
{notifications.length > 3 && (
  <div className="flex justify-center mt-6 gap-4">
    {visibleCount < notifications.length ? (
      <button
        className="btn btn-outline btn-primary"
        onClick={() => setVisibleCount((prev) => prev + 3)}
      >
        👀 Xem thêm
      </button>
    ) : (
      <button
        className="btn btn-outline btn-secondary"
        onClick={() => setVisibleCount(3)}
      >
        🔼 Thu gọn
      </button>
    )}
  </div>
)}

    </div>
  );
}
