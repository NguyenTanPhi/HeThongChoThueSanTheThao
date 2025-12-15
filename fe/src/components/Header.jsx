// src/components/Header.jsx
import { useState, useEffect } from "react";
import { Link, useNavigate, useLocation } from "react-router-dom";
import { axiosPrivate } from "../api/instance";

export default function Header() {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [userName, setUserName] = useState("");

  const navigate = useNavigate();
  const location = useLocation();

  // Luôn kiểm tra lại mỗi khi đổi trang hoặc login/logout
  useEffect(() => {
    const token = localStorage.getItem("token");
    const user = localStorage.getItem("user");

    if (token && user) {
      try {
        const parsed = JSON.parse(user);
        setIsLoggedIn(true);
        setUserName(parsed.name || "");
      } catch {
        setIsLoggedIn(false);
        setUserName("");
      }
    } else {
      setIsLoggedIn(false);
      setUserName("");
    }
  }, [location.pathname]); // 🚀 Fix lỗi không cập nhật state

  const handleLogout = () => {
    localStorage.clear();
    axiosPrivate.defaults.headers.common["Authorization"] = ""; // reset token axios
    setIsLoggedIn(false);
    setUserName("");

    // Reload chuẩn để reset toàn bộ state
    window.location.href = "/login";
  };

  return (
    <>
      <div className="navbar bg-base-100 shadow-md fixed top-0 z-50 px-6">
        
        {/* Logo */}
        <div className="flex-1">
          <Link
            to="/"
            className="btn btn-ghost text-2xl font-bold text-success gap-2"
          >
            ⚽ Đặt Sân Thể Thao
          </Link>
        </div>

        {/* Navigation */}
        <div className="flex-none gap-4 font-medium">

          <Link to="/" className="btn btn-ghost">
            Trang chủ
          </Link>

          <Link to="/about" className="btn btn-ghost">
            Giới thiệu
          </Link>

          <Link to="/contact" className="btn btn-ghost">
            Liên hệ
          </Link>

          {/* Nếu chưa đăng nhập */}
          {!isLoggedIn && (
            <Link
              to="/login"
              className="btn btn-success text-white px-6 rounded-full"
            >
              Đăng nhập
            </Link>
          )}

          {/* Nếu đã đăng nhập */}
          {isLoggedIn && (
            <div className="dropdown dropdown-end">
              <div
                tabIndex={0}
                role="button"
                className="btn btn-ghost rounded-full gap-2"
              >
                <div className="avatar placeholder">
                  <div className="bg-neutral text-neutral-content rounded-full w-9">
                    <span className="text-sm">
                      {userName?.charAt(0)?.toUpperCase()}
                    </span>
                  </div>
                </div>
                {userName}
              </div>

              <ul
                tabIndex={0}
                className="dropdown-content menu p-3 shadow bg-base-100 rounded-box w-44"
              >
                <li>
                  <Link to="/tai-khoan">Trang cá nhân</Link>
                </li>
                <li>
                  <button onClick={handleLogout} className="text-red-500">
                    Đăng xuất
                  </button>
                </li>
              </ul>
            </div>
          )}
        </div>
      </div>

      {/* Chừa khoảng chống cho navbar */}
      <div className="h-20"></div>
    </>
  );
}
