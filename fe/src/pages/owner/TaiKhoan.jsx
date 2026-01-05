// src/pages/owner/TaiKhoan.jsx
import React, { useEffect, useState } from "react";
import { axiosPrivate } from "../../api/instance";
import { useNavigate } from "react-router-dom";

export default function TaiKhoanOwner() {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    phone: "",
    current_password: "",           // Thêm
    password: "",                   // Thêm
    password_confirmation: "",      // Thêm
  });
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [toast, setToast] = useState(null);

  useEffect(() => {
    const fetchUser = async () => {
      try {
        setLoading(true);
        const res = await axiosPrivate.get("/me");
        const userData = res.data || { name: "", email: "", phone: "" };
        setFormData(prev => ({
          ...prev,
          name: userData.name || "",
          email: userData.email || "",
          phone: userData.phone || "",
        }));
      } catch (err) {
        showToast("error", "Không thể tải thông tin người dùng!");
        navigate("/login");
      } finally {
        setLoading(false);
      }
    };
    fetchUser();
  }, [navigate]);

  const showToast = (type, message) => {
    setToast({ type, message });
    setTimeout(() => setToast(null), 3500);
  };

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
    // Xóa lỗi khi sửa
    if (errors[e.target.name]) {
      setErrors({ ...errors, [e.target.name]: "" });
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors({});
    setSubmitting(true);

    // Validation FE cho đổi mật khẩu (nếu có nhập bất kỳ field nào)
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
      showToast("success", "🎉 Cập nhật thông tin thành công!");

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
        const errorMsg =
          serverErrors.current_password?.[0] ||
          serverErrors.password?.[0] ||
          "Lỗi xác thực, vui lòng kiểm tra lại";
        showToast("error", `❌ ${errorMsg}`);
      } else {
        showToast("error", "❌ Lỗi khi cập nhật thông tin!");
      }
    } finally {
      setSubmitting(false);
    }
  };

  // Lấy chữ cái đầu của tên cho avatar
  const getInitial = (name) => (name ? name.charAt(0).toUpperCase() : "?");

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="flex flex-col items-center">
          <span className="loading loading-spinner loading-lg text-primary mb-4"></span>
          <p className="text-lg text-gray-600 font-medium">Đang tải thông tin tài khoản...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 py-10 px-4">
      <div className="max-w-2xl mx-auto bg-white p-8 rounded-2xl shadow-lg">
        {/* Avatar */}
        <div className="flex justify-center mb-6">
          <div className="w-20 h-20 rounded-full bg-gradient-to-br from-blue-600 to-purple-600 text-white flex items-center justify-center text-3xl font-bold shadow-md">
            {getInitial(formData.name)}
          </div>
        </div>

        <h1 className="text-3xl font-bold mb-6 text-center">👤 Quản lý tài khoản chủ sân</h1>

        <form onSubmit={handleSubmit} className="space-y-5">
          <div>
            <label className="block mb-1 font-medium">Họ tên</label>
            <input
              type="text"
              name="name"
              value={formData.name}
              onChange={handleChange}
              className="input input-bordered w-full rounded-lg"
              placeholder="Nhập họ tên"
              required
              disabled={submitting}
            />
            {errors.name && <p className="text-error text-sm mt-1">{errors.name[0]}</p>}
          </div>

          <div>
            <label className="block mb-1 font-medium">Email</label>
            <input
              type="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              className="input input-bordered w-full rounded-lg"
              placeholder="Nhập email"
              required
              disabled={submitting}
            />
            {errors.email && <p className="text-error text-sm mt-1">{errors.email[0]}</p>}
          </div>

          <div>
            <label className="block mb-1 font-medium">Số điện thoại</label>
            <input
              type="text"
              name="phone"
              value={formData.phone}
              onChange={handleChange}
              className="input input-bordered w-full rounded-lg"
              placeholder="Nhập số điện thoại"
              disabled={submitting}
            />
            {errors.phone && <p className="text-error text-sm mt-1">{errors.phone[0]}</p>}
          </div>

          {/* Phần đổi mật khẩu */}
          <div className="mt-8 border-t pt-6">
            <h3 className="text-xl font-bold text-gray-800 mb-4">Đổi mật khẩu (tùy chọn)</h3>

            <div className="space-y-4">
              <div>
                <label className="block mb-1 font-medium">Mật khẩu hiện tại</label>
                <input
                  type="password"
                  name="current_password"
                  value={formData.current_password}
                  onChange={handleChange}
                  className="input input-bordered w-full rounded-lg"
                  placeholder="Nhập nếu bạn muốn đổi mật khẩu"
                  disabled={submitting}
                />
                {errors.current_password && (
                  <p className="text-error text-sm mt-1">{errors.current_password[0]}</p>
                )}
              </div>

              <div>
                <label className="block mb-1 font-medium">Mật khẩu mới</label>
                <input
                  type="password"
                  name="password"
                  value={formData.password}
                  onChange={handleChange}
                  className="input input-bordered w-full rounded-lg"
                  placeholder="Để trống nếu không đổi"
                  disabled={submitting}
                />
                {errors.password && (
                  <p className="text-error text-sm mt-1">{errors.password[0]}</p>
                )}
              </div>

              <div>
                <label className="block mb-1 font-medium">Xác nhận mật khẩu mới</label>
                <input
                  type="password"
                  name="password_confirmation"
                  value={formData.password_confirmation}
                  onChange={handleChange}
                  className="input input-bordered w-full rounded-lg"
                  placeholder="Nhập lại mật khẩu mới"
                  disabled={submitting}
                />
                {errors.password_confirmation && (
                  <p className="text-error text-sm mt-1">{errors.password_confirmation[0]}</p>
                )}
              </div>
            </div>
          </div>

          <button
            type="submit"
            className="btn btn-success w-full mt-2"
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
        </form>
      </div>

      {/* Toast */}
      {toast && (
        <div
          className={`fixed bottom-5 right-5 px-6 py-3 rounded-lg shadow-xl text-white font-medium transition-all flex items-center gap-3 z-50 ${
            toast.type === "success" ? "bg-green-600" : "bg-red-600"
          }`}
        >
          <span>{toast.message}</span>
        </div>
      )}
    </div>
  );
}