import React, { useEffect, useState } from "react";
import { axiosPrivate } from "../../api/instance";
import { toast, ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import { useNavigate } from "react-router-dom";

export default function TaiKhoan() {
  const navigate = useNavigate();
  const [user, setUser] = useState({ name: "", email: "", phone: "" });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchUser = async () => {
      try {
        const res = await axiosPrivate.get("/me");
        setUser(res.data);
      } catch {
        toast.error("❌ Bạn cần đăng nhập để xem trang này");
        navigate("/login");
      } finally {
        setLoading(false);
      }
    };
    fetchUser();
  }, [navigate]);

  const handleChange = (e) => {
    setUser({ ...user, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await axiosPrivate.put("/update-profile", user);
      toast.success("🎉 Cập nhật thông tin thành công!");
    } catch {
      toast.error("❌ Lỗi khi cập nhật thông tin!");
    }
  };

  if (loading) {
    return (
      <div className="p-10 text-center text-gray-500 text-lg">
        Đang tải thông tin...
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-green-50 to-white px-4 py-12">
      <ToastContainer />
      <h1 className="text-4xl font-extrabold mb-10 text-center text-green-700 drop-shadow-md">
        👤 Quản lý tài khoản
      </h1>

      <form
        onSubmit={handleSubmit}
        className="max-w-lg mx-auto bg-white rounded-3xl shadow-2xl border border-gray-200 p-10 space-y-6"
      >
        <div>
          <label className="block text-gray-700 font-semibold mb-2">Họ tên</label>
          <input
            type="text"
            name="name"
            value={user.name}
            onChange={handleChange}
            className="w-full border rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1 shadow-sm"
            required
          />
        </div>

        <div>
          <label className="block text-gray-700 font-semibold mb-2">Email</label>
          <input
            type="email"
            name="email"
            value={user.email}
            onChange={handleChange}
            className="w-full border rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1 shadow-sm"
            required
          />
        </div>

        <div>
          <label className="block text-gray-700 font-semibold mb-2">Số điện thoại</label>
          <input
            type="text"
            name="phone"
            value={user.phone}
            onChange={handleChange}
            className="w-full border rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1 shadow-sm"
          />
        </div>

        <div className="flex justify-end gap-4 mt-6">
          <button
            type="submit"
            className="px-6 py-3 bg-green-600 text-white font-semibold rounded-xl shadow-md hover:bg-green-700 transition"
          >
            💾 Lưu thay đổi
          </button>
          <button
            type="button"
            className="px-6 py-3 bg-gradient-to-r from-green-100 to-green-200 text-green-800 font-semibold rounded-xl shadow hover:from-green-200 hover:to-green-300 transition"
            onClick={() => navigate("/")}
          >
            ⬅ Quay về trang chủ
          </button>
        </div>
      </form>
    </div>
  );
}
