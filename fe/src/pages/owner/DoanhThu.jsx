// src/pages/owner/DoanhThu.jsx
import React, { useState } from "react";
import { axiosPrivate } from "../../api/instance";

export default function DoanhThu() {
  const [filters, setFilters] = useState({
    ngay: "",
    thang: "",
    nam: new Date().getFullYear(),
    from: "",
    to: "",
  });

  const [doanhThu, setDoanhThu] = useState(0);
  const [soDon, setSoDon] = useState(0);
  const [lich, setLich] = useState([]);

  const handleChange = (e) => {
    setFilters({ ...filters, [e.target.name]: e.target.value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const params = new URLSearchParams(filters).toString();
      const res = await axiosPrivate.get(`/owner/thong-ke?${params}`);
      const data = res.data;
      setDoanhThu(data.doanh_thu || 0);
      setSoDon(data.so_don || 0);
      setLich(data.lich || []);
    } catch (err) {
      console.error(err);
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 py-8 px-4">
      <div className="max-w-6xl mx-auto">
        <h1 className="text-4xl font-bold text-center mb-6 text-gray-800">📊 Thống kê doanh thu</h1>

        {/* Filter */}
        <div className="bg-white shadow rounded-lg p-6 mb-6">
          <h2 className="text-xl font-semibold text-gray-700 mb-4">Bộ lọc thống kê</h2>
          <form onSubmit={handleSubmit} className="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
              <label className="block text-gray-600 mb-1">Tháng</label>
              <select
                name="thang"
                className="input input-bordered w-full"
                value={filters.thang}
                onChange={handleChange}
              >
                <option value="">-- Chọn tháng --</option>
                {[...Array(12)].map((_, i) => (
                  <option key={i + 1} value={i + 1}>Tháng {i + 1}</option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-gray-600 mb-1">Năm</label>
              <select
                name="nam"
                className="input input-bordered w-full"
                value={filters.nam}
                onChange={handleChange}
              >
                {[...Array(6)].map((_, i) => {
                  const year = new Date().getFullYear() - i;
                  return <option key={year} value={year}>{year}</option>;
                })}
              </select>
            </div>

            <div className="md:col-span-2">
              <button
                type="submit"
                className="btn btn-primary w-full font-semibold py-2"
              >
                <i className="bi bi-bar-chart-fill me-2"></i>Lọc thống kê
              </button>
            </div>
          </form>
        </div>

        {/* Statistic Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
          <div className="bg-white shadow rounded-lg p-6 text-center">
            <h3 className="text-lg font-semibold text-green-600 mb-2">💰 Tổng doanh thu</h3>
            <p className="text-3xl font-bold text-green-700">
              {new Intl.NumberFormat("vi-VN").format(doanhThu)} đ
            </p>
          </div>
          <div className="bg-white shadow rounded-lg p-6 text-center">
            <h3 className="text-lg font-semibold text-blue-600 mb-2">📦 Tổng số đơn đặt</h3>
            <p className="text-3xl font-bold text-blue-700">{soDon}</p>
          </div>
        </div>

        {/* Table */}
        <div className="bg-white shadow rounded-lg p-6">
          <h3 className="text-lg font-semibold text-gray-700 mb-4">📋 Danh sách đơn đặt</h3>
          {lich.length > 0 ? (
            <div className="overflow-x-auto">
              <table className="table-auto w-full border-collapse">
                <thead className="bg-gray-100">
                  <tr>
                    <th className="p-3 text-left border-b">Thời gian đặt sân</th>
                    <th className="p-3 text-left border-b">Sân</th>
                    <th className="p-3 text-left border-b">Khung giờ</th>
                    <th className="p-3 text-right border-b">Giá</th>
                  </tr>
                </thead>
                <tbody>
                  {lich.map((item, index) => (
                    <tr key={item.id || index} className="hover:bg-gray-50">
                      <td className="p-3 border-b">{new Date(item.created_at).toLocaleString("vi-VN")}</td>
                      <td className="p-3 border-b">
                        <span className="badge badge-info">{item.san?.ten_san || "N/A"}</span>
                      </td>
                      <td className="p-3 border-b">{item.gio_bat_dau} - {item.gio_ket_thuc}</td>
                      <td className="p-3 border-b text-right font-semibold text-green-700">
                        {new Intl.NumberFormat("vi-VN").format(item.tong_gia)} đ
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          ) : (
            <p className="text-gray-500 text-center py-8">Chưa có dữ liệu thống kê.</p>
          )}
        </div>
      </div>
    </div>
  );
}
