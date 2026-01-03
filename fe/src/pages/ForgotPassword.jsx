import { useState } from "react";
import { axiosPublic } from "../api/instance";

export default function ForgotPassword() {
  const [email, setEmail] = useState("");
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState("");
  const [error, setError] = useState("");

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError("");
    setMessage("");

    try {
      await axiosPublic.post("/forgot-password", { email });
      setMessage("Vui lòng kiểm tra email để đặt lại mật khẩu");
    } catch (err) {
      setError("Email không tồn tại trong hệ thống");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex items-center justify-center min-h-screen bg-gray-50 px-4">
      <form
        onSubmit={handleSubmit}
        className="bg-white p-10 rounded-2xl shadow-xl w-full max-w-md"
      >
        <h2 className="text-2xl font-bold mb-6 text-center text-green-600">
          Quên mật khẩu
        </h2>

        {message && (
          <p className="text-green-600 mb-4 text-center">{message}</p>
        )}
        {error && (
          <p className="text-red-500 mb-4 text-center">{error}</p>
        )}

        <input
          type="email"
          placeholder="Nhập email"
          className="input input-bordered w-full mb-4"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
          disabled={loading}
        />

        <button
          type="submit"
          disabled={loading}
          className="btn btn-success w-full"
        >
          {loading ? "Đang gửi..." : "Gửi link đặt lại mật khẩu"}
        </button>
      </form>
    </div>
  );
}
