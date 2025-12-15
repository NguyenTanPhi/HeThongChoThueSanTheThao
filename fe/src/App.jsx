import React from "react";
import { Routes, Route, Outlet } from "react-router-dom";

// Layout components
import Header from "./components/Header";
import Footer from "./components/Footer";

// Pages
import Home from "./pages/customer/Home.jsx";
import SanDetail from "./pages/customer/SanDetail.jsx";
import ThanhToan from "./pages/customer/ThanhToan";
import VnpayReturn from "./pages/customer/VnpayReturn";
import LichSuDat from "./pages/customer/LichSuDat.jsx";
import TaiKhoan from "./pages/customer/TaiKhoan.jsx";
import Dashboard from "./pages/owner/Dashboard.jsx";
import Register from "./components/Register.jsx";
import ConfirmEmailSuccess from "./pages/ConfirmEmailSuccess.jsx";
import Login from "./pages/Login";
import ChiTietSanOwner from "./pages/owner/ChiTietSanOwner.jsx";
import ThanhToanOwner from "./pages/owner/ThanhToanOwner.jsx";
import VnpayReturnOwner from "./pages/owner/VnpayReturnOwner.jsx";
import GoiDichVu from "./pages/owner/GoiDichVu.jsx";
import Notification from "./pages/owner/Notification.jsx";
import DashboardAdmin from "./pages/admin/DashboardAdmin.jsx";
import About from "./pages/About";
import Contact from "./pages/Contact";

// Layout cho khách hàng: có header + footer
function CustomerLayout() {
  return (
    <>
      <Header />
      <div className="min-h-[70vh]">
        <Outlet />
      </div>
      <Footer />
    </>
  );
}

// Layout cho Owner/Admin: không có header/footer
function OwnerLayout() {
  return (
    <div className="min-h-screen">
      <Outlet />
    </div>
  );
}

function App() {
  return (
    <Routes>
      {/* Customer routes */}
      <Route element={<CustomerLayout />}>
        <Route path="/" element={<Home />} />
        <Route path="/login" element={<Login />} />
        <Route path="/register" element={<Register />} />
        <Route path="/san/:id" element={<SanDetail />} />
        <Route path="/thanh-toan" element={<ThanhToan />} />
        <Route path="/vnpay-return" element={<VnpayReturn />} />
        <Route path="/lich-su-dat" element={<LichSuDat />} />
        <Route path="/tai-khoan" element={<TaiKhoan />} />
        <Route path="/about" element={<About />} />
        <Route path="/contact" element={<Contact />} />
        <Route path="/open/confirmEmail" element={<ConfirmEmailSuccess />} />
      </Route>

      {/* Owner routes */}
      <Route element={<OwnerLayout />}>
        <Route path="/owner/dashboard" element={<Dashboard />} />
        <Route path="/owner/san/:id" element={<ChiTietSanOwner />} />
        <Route path="/owner/thanh-toan" element={<ThanhToanOwner />} />
        <Route path="/owner/vnpay-return" element={<VnpayReturnOwner />} />
        <Route path="/owner/goi-dich-vu" element={<GoiDichVu />} />
        <Route path="/owner/notifications" element={<Notification />} />
      </Route>

      {/* Admin routes */}
      <Route element={<OwnerLayout />}>
        <Route path="/admin/dashboard" element={<DashboardAdmin />} />
      </Route>
    </Routes>
  );
}

export default App;
