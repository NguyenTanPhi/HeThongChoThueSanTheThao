import React, { useState } from "react";
import { useNavigate, Link } from "react-router-dom";
import { axiosPublic } from "../api/instance";

export default function Register() {
  const [formData, setFormData] = useState({
    name: "",
    email: "",
    password: "",
    phone: "",
    role: "customer",
  });
  const [message, setMessage] = useState(""); //hiển thị thông báo chung 
  const [messageType, setMessageType] = useState(""); // success hoặc error
  const [errors, setErrors] = useState({});
  const [isSubmitting, setIsSubmitting] = useState(false); // Trạng thái loading khi submit
  const navigate = useNavigate(); //điều hướng

  const handleChange = (e) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
    // Xóa lỗi cũ khi người dùng sửa
    if (errors[e.target.name]) {
      setErrors({ ...errors, [e.target.name]: [] });
    }
  };
// xóa lỗi cũ khi người dùng sửa
  const handleSubmit = async (e) => {
    e.preventDefault();
    setErrors({});
    setMessage(""); 
    setMessageType(""); 
    setIsSubmitting(true); // Bật loading

    try {
      const res = await axiosPublic.post("/register", formData);

      if (formData.role === "customer") {
        localStorage.setItem("token", res.data.token); // Lưu token
        localStorage.setItem("user", JSON.stringify(res.data.user)); // Lưu thông tin user
        setMessage("🎉 Đăng ký thành công, bạn đã được đăng nhập!");
        setMessageType("success");
        setTimeout(() => navigate("/"), 1500); // Chuyển trang sau 1.5s để thấy message
      } else {
        setMessage(res.data.message || "✅ Đăng ký thành công! Vui lòng kiểm tra email để xác nhận.");
        setMessageType("success");
      }
    } catch (err) {
      if (err.response?.status === 422) {
        setErrors(err.response.data.errors || {});
      } else {
        setMessage("❌ Có lỗi xảy ra, vui lòng thử lại!");
        setMessageType("error");
      }
    } finally {
      setIsSubmitting(false); // Tắt loading dù thành công hay lỗi
    }
  };

  return (
    <div className="flex items-center justify-center min-h-screen bg-gray-50 px-4">
      <div className="w-full max-w-md">
        <div className="bg-white shadow-xl rounded-2xl p-10 border border-gray-200">
          <h2 className="text-3xl font-bold text-center text-green-600 mb-6">
            Đăng ký tài khoản
          </h2>

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <input
                type="text"
                name="name"
                placeholder="Họ tên"
                value={formData.name}
                onChange={handleChange}
                className="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                required
                disabled={isSubmitting}
              />
              {errors.name && <p className="text-red-500 text-sm mt-1">{errors.name[0]}</p>}
            </div>

            <div>
              <input
                type="email"
                name="email"
                placeholder="Email"
                value={formData.email}
                onChange={handleChange}
                className="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                required
                disabled={isSubmitting}
              />
              {errors.email && <p className="text-red-500 text-sm mt-1">{errors.email[0]}</p>}
            </div>

            <div>
              <input
                type="password"
                name="password"
                placeholder="Mật khẩu"
                value={formData.password}
                onChange={handleChange}
                className="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                required
                disabled={isSubmitting}
              />
              {errors.password && <p className="text-red-500 text-sm mt-1">{errors.password[0]}</p>}
            </div>

            <div>
              <input
                type="text"
                name="phone"
                placeholder="Số điện thoại"
                value={formData.phone}
                onChange={handleChange}
                className="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                disabled={isSubmitting}
              />
              {errors.phone && <p className="text-red-500 text-sm mt-1">{errors.phone[0]}</p>}
            </div>

            <div>
              <select
                name="role"
                value={formData.role}
                onChange={handleChange}
                className="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                disabled={isSubmitting}
              >
                <option value="customer">Khách hàng</option>
                <option value="owner">Chủ sân</option>
              </select>
              {errors.role && <p className="text-red-500 text-sm mt-1">{errors.role[0]}</p>}
            </div>

            <button
              type="submit"
              className={`w-full py-3 rounded-lg font-medium text-lg shadow-md transition flex items-center justify-center gap-2
                ${isSubmitting 
                  ? "bg-green-400 cursor-not-allowed" 
                  : "bg-green-600 hover:bg-green-700 text-white"}`}
              disabled={isSubmitting}
            >
              {isSubmitting ? (
                <>
                  <span className="loading loading-spinner loading-sm"></span>
                  Đang đăng ký...
                </>
              ) : (
                "Đăng ký"
              )}
            </button>
          </form>

          {message && (
            <div
              className={`mt-6 p-3 rounded text-center font-medium ${
                messageType === "success"
                  ? "bg-green-100 text-green-700 border border-green-300"
                  : "bg-red-100 text-red-700 border border-red-300"
              }`}
            >
              {message}
            </div>
          )}

          <p className="mt-6 text-center text-gray-500 text-sm">
            Đã có tài khoản?
            <Link to="/login" className="text-green-600 font-medium ml-1 hover:underline">
              Đăng nhập
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}