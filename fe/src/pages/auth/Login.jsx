import { useState } from "react";
import { axiosPublic } from "../../api/instance";
import { useAuth } from "../../Context/AuthContext";
import { useNavigate } from "react-router-dom";

export default function Login() {
  const [form, setForm] = useState({ email: "", password: "" });
  const [loading, setLoading] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      const res = await axiosPublic.post("/login", form);
      login(res.data.token, res.data.user);
      
      // Chuyển hướng theo role
      if (res.data.user.role === "admin") navigate("/admin");
      else if (res.data.user.role === "owner") navigate("/owner");
      else navigate("/");
    } catch (err) {
      alert(err.response?.data?.message || "Đăng nhập thất bại");
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className="max-w-md mx-auto mt-10 p-6 bg-white rounded shadow">
      <h2 className="text-2xl font-bold mb-6">Đăng nhập</h2>
      <input
        type="email"
        placeholder="Email"
        className="w-full p-3 border mb-4 rounded"
        value={form.email}
        onChange={(e) => setForm({ ...form, email: e.target.value })}
        required
      />
      <input
        type="password"
        placeholder="Mật khẩu"
        className="w-full p-3 border mb-6 rounded"
        value={form.password}
        onChange={(e) => setForm({ ...form, password: e.target.value })}
        required
      />
      <button
        type="submit"
        disabled={loading}
        className="w-full bg-blue-600 text-white py-3 rounded hover:bg-blue-700 disabled:opacity-50"
      >
        {loading ? "Đang đăng nhập..." : "Đăng nhập"}
      </button>
    </form>
  );
}