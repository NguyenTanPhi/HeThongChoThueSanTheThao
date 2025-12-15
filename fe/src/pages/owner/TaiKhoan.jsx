// src/pages/owner/TaiKhoan.jsx
import React, { useState, useEffect } from "react";
import { axiosPrivate } from "../../api/instance";

export default function TaiKhoanOwner() {
  const [user, setUser] = useState({ name: "", email: "", phone: "" });
  const [toast, setToast] = useState(null);

  useEffect(() => {
    const fetchUser = async () => {
      try {
        const res = await axiosPrivate.get("/me");
        setUser(res.data);
      } catch (err) {
        showToast("error", "Không thể tải thông tin người dùng!");
      }
    };
    fetchUser();
  }, []);

  const showToast = (type, message) => {
    setToast({ type, message });
    setTimeout(() => setToast(null), 3000);
  };

  const handleChange = (e) => {
    setUser({ ...user, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await axiosPrivate.put("/update-profile", user);
      showToast("success", "🎉 Cập nhật thành công!");
    } catch (err) {
      showToast("error", "❌ Lỗi khi cập nhật! Vui lòng thử lại.");
    }
  };

  // Lấy chữ cái đầu của tên
  const getInitial = (name) => (name ? name.charAt(0).toUpperCase() : "?");

  return (
    <div className="min-h-screen bg-gray-50 py-10 px-4">
      <div className="max-w-2xl mx-auto bg-white p-8 rounded-2xl shadow-lg">
        {/* Avatar/logo */}
        <div className="flex justify-center mb-6">
          <div className="w-20 h-20 rounded-full bg-gradient-to-br from-blue-600 to-purple-600 text-white flex items-center justify-center text-3xl font-bold shadow-md">
            {getInitial(user.name)}
          </div>
        </div>

        <h1 className="text-3xl font-bold mb-6 text-center">👤 Quản lý tài khoản chủ sân</h1>

        <form onSubmit={handleSubmit} className="space-y-5">
          <div>
            <label className="block mb-1 font-medium">Họ tên</label>
            <input
              type="text"
              name="name"
              value={user.name}
              onChange={handleChange}
              className="input input-bordered w-full rounded-lg"
              placeholder="Nhập họ tên"
              required
            />
          </div>
          <div>
            <label className="block mb-1 font-medium">Email</label>
            <input
              type="email"
              name="email"
              value={user.email}
              onChange={handleChange}
              className="input input-bordered w-full rounded-lg"
              placeholder="Nhập email"
              required
            />
          </div>
          <div>
            <label className="block mb-1 font-medium">Số điện thoại</label>
            <input
              type="text"
              name="phone"
              value={user.phone}
              onChange={handleChange}
              className="input input-bordered w-full rounded-lg"
              placeholder="Nhập số điện thoại"
            />
          </div>
          <button type="submit" className="btn btn-success w-full mt-2">
            💾 Lưu thay đổi
          </button>
        </form>
      </div>

      {/* Toast */}
      {toast && (
        <div
          className={`fixed bottom-5 right-5 px-6 py-3 rounded-lg shadow-xl text-white font-medium transition-all flex items-center justify-between ${
            toast.type === "success" ? "bg-green-600" : "bg-red-600"
          }`}
        >
          <span>{toast.message}</span>
        </div>
      )}
    </div>
  );
}
