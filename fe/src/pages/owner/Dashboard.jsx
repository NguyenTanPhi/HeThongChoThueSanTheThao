// src/pages/owner/Dashboard.jsx
import React, { useState, useEffect } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import QuanLySan from "./QuanLySan";
import DonDatSan from "./DonDatSan";
import TaiKhoanOwner from "./TaiKhoan";
import DoanhThu from "./DoanhThu";
import GoiDichVu from "./GoiDichVu";
import Notification from "./Notification";
import { axiosPrivate } from "../../api/instance";

export default function OwnerDashboard() {
  const [activeTab, setActiveTab] = useState("san");
  const navigate = useNavigate();
  const location = useLocation();

  // Đọc query param khi load hoặc URL thay đổi
  useEffect(() => {
    const params = new URLSearchParams(location.search);
    const tab = params.get("tab");
    if (tab) setActiveTab(tab);
  }, [location]);

  // Đổi tab + update URL
  const handleTabChange = (tab) => {
    setActiveTab(tab);
    navigate(`/owner/dashboard?tab=${tab}`);
  };

  // Logout
  const handleLogout = () => {
    localStorage.clear();
    delete axiosPrivate.defaults.headers.common["Authorization"];
    navigate("/login");
  };

  const sidebarItems = [
    { key: "san", label: "Quản lý sân", icon: "🏟️" },
    { key: "don", label: "Đơn đặt sân", icon: "📋" },
    { key: "tai-khoan", label: "Tài khoản", icon: "👤" },
    { key: "doanh-thu", label: "Doanh thu", icon: "💰" },
    { key: "goi-dich-vu", label: "Gói dịch vụ", icon: "📦" },
    { key: "thong-bao", label: "Thông báo", icon: "🔔" },
  ];

  return (
    <div className="flex min-h-screen bg-gray-100">
      {/* Sidebar */}
      <aside className="w-64 bg-green-700 text-white flex flex-col justify-between p-6 shadow-lg">
        <div>
          <h2 className="text-2xl font-bold mb-6 flex items-center gap-2">
            🏟️ Chủ sân
          </h2>

          <nav className="flex flex-col gap-2">
            {sidebarItems.map((item) => (
              <button
                key={item.key}
                className={`flex items-center gap-2 px-4 py-2 rounded-lg transition-colors text-left
                  ${
                    activeTab === item.key
                      ? "bg-green-800 font-semibold shadow-inner"
                      : "hover:bg-green-600"
                  }`}
                onClick={() => handleTabChange(item.key)}
              >
                <span className="text-lg">{item.icon}</span> {item.label}
              </button>
            ))}
          </nav>
        </div>

        <button
          onClick={handleLogout}
          className="mt-6 w-full px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 transition-colors font-semibold"
        >
          🔒 Đăng xuất
        </button>
      </aside>

      {/* Content */}
      <main className="flex-1 p-8 overflow-auto">
        {activeTab === "san" && <QuanLySan setActiveTab={setActiveTab} />}
        {activeTab === "don" && <DonDatSan />}
        {activeTab === "tai-khoan" && <TaiKhoanOwner />}
        {activeTab === "doanh-thu" && <DoanhThu />}
        {activeTab === "goi-dich-vu" && <GoiDichVu />}
        {activeTab === "thong-bao" && <Notification />}
      </main>
    </div>
  );
}
