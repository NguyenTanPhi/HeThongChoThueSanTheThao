import React from "react";
import { Routes, Route, Navigate } from "react-router-dom";
import { getRole } from "../services/authService";

// Customer pages
import Home from "../pages/customer/Home";
import SanList from "../pages/customer/SanList";
import Booking from "../pages/customer/Booking";

// Owner pages
import OwnerDashboard from "../pages/owner/Dashboard";
import LichTrong from "../pages/owner/LichTrong";
import Notifications from "../pages/owner/Notification";

// Admin pages
import AdminDashboard from "../pages/admin/Dashboard";
import Users from "../pages/admin/User";
import SansDuyet from "../pages/admin/SanDuyet";

/**
 * ✅ Route bảo vệ theo role
 * - Chưa đăng nhập → về /
 * - Sai role → về /
 */
function PrivateRoute({ role, children }) {
  const userRole = getRole(); // ví dụ: "admin" | "owner" | null
 console.log("PRIVATE ROUTE CHECK");
  if (!userRole) {
    // ❌ Chưa đăng nhập
    return <Navigate to="/" replace />;
  }

  if (userRole !== role) {
    // ❌ Sai quyền
    return <Navigate to="/" replace />;
  }

  return children;
}

export default function AppRouter() {
  return (
    
      <Routes>
        {/* ================= CUSTOMER ================= */}
        <Route path="/" element={<Home />} />
        <Route path="/sans" element={<SanList />} />
        <Route path="/booking/:id" element={<Booking />} />

        {/* ================= OWNER ================= */}
        <Route
          path="/owner/dashboard"
          element={
            <PrivateRoute role="owner">
              <OwnerDashboard />
            </PrivateRoute>
          }
        />

        <Route
          path="/owner/lich-trong/:id"
          element={
            <PrivateRoute role="owner">
              <LichTrong />
            </PrivateRoute>
          }
        />

        <Route
          path="/owner/notifications"
          element={
            <PrivateRoute role="owner">
              <Notifications />
            </PrivateRoute>
          }
        />

        {/* ================= ADMIN ================= */}
        <Route
          path="/admin/dashboard"
          element={
            <PrivateRoute role="admin">
              <AdminDashboard />
            </PrivateRoute>
          }
        />

        <Route
          path="/admin/users"
          element={
            <PrivateRoute role="admin">
              <Users />
            </PrivateRoute>
          }
        />

        <Route
          path="/admin/sans-cho-duyet"
          element={
            <PrivateRoute role="admin">
              <SansDuyet />
            </PrivateRoute>
          }
        />

        {/* ================= FALLBACK ================= */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    
  );
}
