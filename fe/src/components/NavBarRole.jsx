import React from "react";
import { Link } from "react-router-dom";
import { getRole } from "../services/authService";

export default function NavBarRole() {
  const role = getRole() || "customer"; 

  if (role === "admin") {
    return (
      <nav style={{ padding: "1rem", background: "#eee" }}>
        <Link to="/admin/dashboard" style={{ margin: "0 1rem" }}>Dashboard</Link>
        <Link to="/admin/users" style={{ margin: "0 1rem" }}>Users</Link>
        <Link to="/admin/sans-cho-duyet" style={{ margin: "0 1rem" }}>Sân chờ duyệt</Link>
      </nav>
    );
  }

  if (role === "owner") {
    return (
      <nav style={{ padding: "1rem", background: "#eee" }}>
        <Link to="/owner/dashboard" style={{ margin: "0 1rem" }}>Dashboard</Link>
        <Link to="/owner/my-sans" style={{ margin: "0 1rem" }}>Sân của tôi</Link>
        <Link to="/owner/notifications" style={{ margin: "0 1rem" }}>Thông báo</Link>
      </nav>
    );
  }

  // Customer
  return (
    <nav style={{ padding: "1rem", background: "#eee" }}>
      <Link to="/" style={{ margin: "0 1rem" }}>Trang chủ</Link>
      <Link to="/sans" style={{ margin: "0 1rem" }}>Danh sách sân</Link>
    </nav>
  );
}
