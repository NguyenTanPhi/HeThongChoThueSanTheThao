// src/pages/admin/Dashboard.jsx
import React, { useState, useEffect } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import SanChoDuyet from "./SanChoDuyet";
import QuanLyUser from "./QuanLyUser";
import QuanLyGoiDichVu from "./QuanLyGoiDichVu";
import BaoCao from "./BaoCao";
import { axiosPrivate } from "../../api/instance";

export default function AdminDashboard() {
  const [activeTab, setActiveTab] = useState("dashboard");
  const navigate = useNavigate();
  const location = useLocation();

  useEffect(() => {
    const params = new URLSearchParams(location.search);
    const tab = params.get("tab");
    if (tab) setActiveTab(tab);
  }, [location]);

  const handleTabChange = (tab) => {
    setActiveTab(tab);
    navigate(`/admin/dashboard?tab=${tab}`);
  };

  const handleLogout = () => {
    localStorage.clear();
    delete axiosPrivate.defaults.headers.common["Authorization"];
    navigate("/login");
  };

  const menuItems = [
    { id: "dashboard", label: "Tổng quan", icon: "📊" },
    { id: "san-cho-duyet", label: "Sân chờ duyệt", icon: "✅" },
    { id: "users", label: "Quản lý người dùng", icon: "👥" },
    { id: "goi-dich-vu", label: "Gói dịch vụ", icon: "📦" },
    { id: "bao-cao", label: "Báo cáo thống kê", icon: "📈" },
  ];

  return (
    <div className="flex min-h-screen bg-gray-100">
      {/* Sidebar */}
      <aside className="w-64 bg-white shadow-xl border-r">
        <div className="p-6 border-b">
          <h2 className="text-2xl font-bold text-blue-700">Admin Panel</h2>
          <p className="text-sm text-gray-500 mt-1">Quản trị hệ thống</p>
        </div>

        <nav className="mt-4">
          {menuItems.map((item) => (
            <button
              key={item.id}
              onClick={() => handleTabChange(item.id)}
              className={`w-full flex items-center gap-3 px-6 py-3 text-left font-medium transition-all
                ${
                  activeTab === item.id
                    ? "bg-blue-600 text-white shadow-md"
                    : "text-gray-700 hover:bg-blue-50"
                }`}
            >
              <span className="text-xl">{item.icon}</span>
              {item.label}
            </button>
          ))}

          <button
            onClick={handleLogout}
            className="w-full mt-6 px-6 py-3 text-left text-red-600 font-semibold hover:bg-red-50 transition-all"
          >
            🚪 Đăng xuất
          </button>
        </nav>
      </aside>

      {/* Content */}
      <main className="flex-1 p-10">
       {activeTab === "dashboard" && (
  <div>
    <h1 className="text-3xl font-bold mb-6">📊 Dashboard Admin</h1>
    <p className="text-gray-600 text-lg">
      Chào mừng bạn đến trang quản trị hệ thống.
    </p>
  </div>
)}


        {activeTab === "san-cho-duyet" && <SanChoDuyet />}
        {activeTab === "users" && <QuanLyUser />}
        {activeTab === "goi-dich-vu" && <QuanLyGoiDichVu />}
        {activeTab === "bao-cao" && <BaoCao />}
      </main>
    </div>
  );
}
