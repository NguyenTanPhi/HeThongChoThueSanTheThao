import { useState } from "react";
import { useNavigate, Link } from "react-router-dom";
import { axiosPublic } from "../api/instance";

export default function Login() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const navigate = useNavigate();

  const handleLogin = async (e) => {
    e.preventDefault();
    try {
      const res = await axiosPublic.post("/login", { email, password });
      localStorage.setItem("token", res.data.token);
      localStorage.setItem("user", JSON.stringify(res.data.user));

      const role = res.data.user.role;

      if (role === "owner") {
        navigate("/owner/dashboard");
      } else if (role === "admin") {
        navigate("/admin/dashboard");
      } else {
        navigate("/"); // customer
      }
    } catch (err) {
      setError("Sai email hoặc mật khẩu");
    }
  };

  return (
    <div className="flex items-center justify-center min-h-screen bg-gray-50 px-4">
      <div className="w-full max-w-md">
        <form 
          onSubmit={handleLogin} 
          className="bg-white p-10 rounded-2xl shadow-xl border border-gray-200"
        >
          <h2 className="text-3xl font-bold mb-6 text-center text-green-600">
            Đăng nhập
          </h2>

          {error && (
            <p className="text-red-500 mb-4 text-center font-medium">{error}</p>
          )}

          <div className="space-y-4">
            <input
              type="email"
              placeholder="Email"
              className="input input-bordered w-full rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />

            <input
              type="password"
              placeholder="Mật khẩu"
              className="input input-bordered w-full rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </div>

          <button
            type="submit"
            className="btn btn-success w-full mt-6 text-white text-lg rounded-lg shadow-md hover:bg-green-700 transition"
          >
            Đăng nhập
          </button>

          <p className="mt-6 text-center text-gray-500 text-sm">
            Chưa có tài khoản?
          </p>

          <div className="flex justify-center gap-4 mt-3">
            <Link
              to="/register"
              className="btn btn-outline btn-sm text-green-600 border-green-500 hover:bg-green-100"
            >
              Đăng ký ngay
            </Link>
            <Link
              to="/"
              className="btn btn-outline btn-sm text-gray-600 border-gray-300 hover:bg-gray-100"
            >
              Trở lại trang chủ
            </Link>
          </div>
        </form>
      </div>
    </div>
  );
}
