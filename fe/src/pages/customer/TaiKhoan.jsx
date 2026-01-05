import React, { useEffect, useState } from "react";
import { axiosPrivate } from "../../api/instance";
import { toast, ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import { useNavigate } from "react-router-dom";

export default function TaiKhoan() {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    phone: "",
    current_password: "",
    password: "",
    password_confirmation: "",
  });
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    const fetchUser = async () => {
      try {
        const res = await axiosPrivate.get("/me");
        setFormData(prev => ({
          ...prev,
          name: res.data.name || "",
          email: res.data.email || "",
          phone: res.data.phone || "",
        }));
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
    setFormData({ ...formData, [e.target.name]: e.target.value });
    // Xóa lỗi khi người dùng sửa
    if (errors[e.target.name]) {
      setErrors({ ...errors, [e.target.name]: "" });
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors({});
    setSubmitting(true);

    // Validation FE cho đổi mật khẩu (nếu có nhập)
    if (formData.password || formData.password_confirmation || formData.current_password) {
      if (formData.password.length < 6) {
        setErrors({ password: "Mật khẩu mới phải ít nhất 6 ký tự" });
        setSubmitting(false);
        return;
      }
      if (formData.password !== formData.password_confirmation) {
        setErrors({ password_confirmation: "Mật khẩu xác nhận không khớp" });
        setSubmitting(false);
        return;
      }
      if (!formData.current_password) {
        setErrors({ current_password: "Vui lòng nhập mật khẩu hiện tại để đổi mật khẩu mới" });
        setSubmitting(false);
        return;
      }
    }

    try {
      await axiosPrivate.put("/update-profile", formData);
      toast.success("🎉 Cập nhật thông tin thành công!");

      // Reset field password sau khi thành công
      setFormData(prev => ({
        ...prev,
        current_password: "",
        password: "",
        password_confirmation: "",
      }));
    } catch (err) {
      if (err.response?.status === 422) {
        const serverErrors = err.response.data.errors || {};
        setErrors(serverErrors);

        // Hiển thị toast cho lỗi phổ biến
        const errorMsg =
          serverErrors.current_password?.[0] ||
          serverErrors.password?.[0] ||
          "Lỗi xác thực, vui lòng kiểm tra lại";
        toast.error(`❌ ${errorMsg}`);
      } else {
        toast.error("❌ Lỗi khi cập nhật thông tin!");
      }
    } finally {
      setSubmitting(false);
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
      <ToastContainer position="top-right" autoClose={3000} />
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
            value={formData.name}
            onChange={handleChange}
            className="w-full border rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 shadow-sm"
            required
            disabled={submitting}
          />
          {errors.name && <p className="text-red-500 text-sm mt-1">{errors.name[0]}</p>}
        </div>

        <div>
          <label className="block text-gray-700 font-semibold mb-2">Email</label>
          <input
            type="email"
            name="email"
            value={formData.email}
            onChange={handleChange}
            className="w-full border rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 shadow-sm"
            required
            disabled={submitting}
          />
          {errors.email && <p className="text-red-500 text-sm mt-1">{errors.email[0]}</p>}
        </div>

        <div>
          <label className="block text-gray-700 font-semibold mb-2">Số điện thoại</label>
          <input
            type="text"
            name="phone"
            value={formData.phone}
            onChange={handleChange}
            className="w-full border rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 shadow-sm"
            disabled={submitting}
          />
          {errors.phone && <p className="text-red-500 text-sm mt-1">{errors.phone[0]}</p>}
        </div>

        {/* Phần đổi mật khẩu */}
        <div className="mt-8 border-t pt-6">
          <h3 className="text-xl font-bold text-gray-800 mb-4">Đổi mật khẩu (tùy chọn)</h3>

          <div className="space-y-4">
            <div>
              <label className="block text-gray-700 font-semibold mb-2">Mật khẩu hiện tại</label>
              <input
                type="password"
                name="current_password"
                value={formData.current_password}
                onChange={handleChange}
                className="w-full border rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 shadow-sm"
                placeholder="Nhập nếu bạn muốn đổi mật khẩu"
                disabled={submitting}
              />
              {errors.current_password && (
                <p className="text-red-500 text-sm mt-1">{errors.current_password[0]}</p>
              )}
            </div>

            <div>
              <label className="block text-gray-700 font-semibold mb-2">Mật khẩu mới</label>
              <input
                type="password"
                name="password"
                value={formData.password}
                onChange={handleChange}
                className="w-full border rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 shadow-sm"
                placeholder="Để trống nếu không đổi"
                disabled={submitting}
              />
              {errors.password && (
                <p className="text-red-500 text-sm mt-1">{errors.password[0]}</p>
              )}
            </div>

            <div>
              <label className="block text-gray-700 font-semibold mb-2">Xác nhận mật khẩu mới</label>
              <input
                type="password"
                name="password_confirmation"
                value={formData.password_confirmation}
                onChange={handleChange}
                className="w-full border rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500 shadow-sm"
                placeholder="Nhập lại mật khẩu mới"
                disabled={submitting}
              />
              {errors.password_confirmation && (
                <p className="text-red-500 text-sm mt-1">{errors.password_confirmation[0]}</p>
              )}
            </div>
          </div>
        </div>

        <div className="flex justify-end gap-4 mt-8">
          <button
            type="submit"
            className={`px-6 py-3 bg-green-600 text-white font-semibold rounded-xl shadow-md hover:bg-green-700 transition flex items-center gap-2
              ${submitting ? "opacity-70 cursor-not-allowed" : ""}`}
            disabled={submitting}
          >
            {submitting ? (
              <>
                <span className="loading loading-spinner loading-sm"></span>
                Đang lưu...
              </>
            ) : (
              "💾 Lưu thay đổi"
            )}
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