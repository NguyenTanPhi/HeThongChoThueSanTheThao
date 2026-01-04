import React, { useEffect, useState } from "react";
import { getSanList } from "../../api/san.js";
import SanCard from "../../components/SanCard.jsx";
import Header from "../../components/Header";
import { useNavigate } from "react-router-dom";

export default function Home() {
  const [sanList, setSanList] = useState([]);
  const [loading, setLoading] = useState(true);

  // ✅ FILTER STATE
  const [filters, setFilters] = useState({
    keyword: "",
    dia_chi: "",
    minPrice: "",
    maxPrice: "",
  });

  const navigate = useNavigate();

  useEffect(() => {
    getSanList()
      .then((res) => {
        setSanList(res.data.data || []);
        setLoading(false);
      })
      .catch(() => setLoading(false));
  }, []);

  // ✅ FILTER LOGIC
  const filteredSanList = sanList.filter((san) => {
    const matchName = san.ten_san
      .toLowerCase()
      .includes(filters.keyword.toLowerCase());

    const matchAddress = san.dia_chi
      .toLowerCase()
      .includes(filters.dia_chi.toLowerCase());

    const matchMin =
      !filters.minPrice || san.gia_thue >= Number(filters.minPrice);

    const matchMax =
      !filters.maxPrice || san.gia_thue <= Number(filters.maxPrice);

    return matchName && matchAddress && matchMin && matchMax;
  });

  return (
    <>
      <Header />

      {/* HERO */}
      <div className="relative h-[480px] flex items-center justify-center overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-green-600 to-emerald-800 opacity-95"></div>
        <div className="absolute inset-0 bg-[url('https://heropatterns.com/patterns/microbial-mat.png')] opacity-15"></div>

        <div className="relative z-10 text-center text-white px-4">
          <h1 className="text-6xl font-bold mb-6">
            Đặt Sân Bóng Nhanh Chóng
          </h1>

          <div className="flex gap-4 justify-center">
            <a href="#danh-sach-san" className="btn bg-white text-green-700">
              🔍 Tìm sân ngay
            </a>
            <button onClick={() => navigate("/lich-su-dat")} className="btn btn-warning">
              📖 Lịch sử đặt sân
            </button>
            <button onClick={() => navigate("/tai-khoan")} className="btn btn-success text-white">
              👤 Tài khoản
            </button>
          </div>
        </div>
      </div>

      {/* DANH SÁCH */}
      <div id="danh-sach-san" className="container mx-auto px-5 py-16 bg-gray-50">

        <h2 className="text-4xl font-bold text-center mb-10">
          Các sân bóng nổi bật
        </h2>

        {/* FILTER */}
<div className="bg-white p-6 rounded-3xl shadow-lg mb-10 border border-gray-100">
  <div className="grid grid-cols-1 md:grid-cols-4 gap-4">

    {/* TÊN SÂN */}
    <div className="relative">
      <span className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
        🔍
      </span>
      <input
        placeholder="Tên sân"
        className="input input-bordered w-full pl-10 focus:ring-2 focus:ring-green-500"
        value={filters.keyword}
        onChange={(e) =>
          setFilters({ ...filters, keyword: e.target.value })
        }
      />
    </div>

    {/* ĐỊA CHỈ */}
    <div className="relative">
      <span className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
        📍
      </span>
      <input
        placeholder="Khu vực / Địa chỉ"
        className="input input-bordered w-full pl-10 focus:ring-2 focus:ring-green-500"
        value={filters.dia_chi}
        onChange={(e) =>
          setFilters({ ...filters, dia_chi: e.target.value })
        }
      />
    </div>

    {/* GIÁ TỪ */}
    <div className="relative">
      <span className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
        💰
      </span>
      <input
        type="number"
        placeholder="Giá từ"
        className="input input-bordered w-full pl-10 focus:ring-2 focus:ring-green-500"
        value={filters.minPrice}
        onChange={(e) =>
          setFilters({ ...filters, minPrice: e.target.value })
        }
      />
    </div>

    {/* GIÁ ĐẾN */}
    <div className="relative">
      <span className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
        💸
      </span>
      <input
        type="number"
        placeholder="Giá đến"
        className="input input-bordered w-full pl-10 focus:ring-2 focus:ring-green-500"
        value={filters.maxPrice}
        onChange={(e) =>
          setFilters({ ...filters, maxPrice: e.target.value })
        }
      />
    </div>
  </div>

  {/* ACTION */}
  <div className="flex justify-between items-center mt-6">
    <p className="text-sm text-gray-500">
      🔎 Tìm thấy <b>{filteredSanList.length}</b> sân phù hợp
    </p>

    <button
      className="btn btn-outline btn-sm hover:bg-green-50"
      onClick={() =>
        setFilters({
          keyword: "",
          dia_chi: "",
          minPrice: "",
          maxPrice: "",
        })
      }
    >
      🔄 Xóa bộ lọc
    </button>
  </div>
</div>


        {/* TAG ĐANG LỌC */}
        <div className="flex flex-wrap gap-2 mb-6">
          {filters.keyword && <span className="badge">Tên: {filters.keyword}</span>}
          {filters.dia_chi && <span className="badge">Địa chỉ: {filters.dia_chi}</span>}
          {filters.minPrice && <span className="badge">
            Giá từ: {Number(filters.minPrice).toLocaleString()}đ
          </span>}
          {filters.maxPrice && <span className="badge">
            Giá đến: {Number(filters.maxPrice).toLocaleString()}đ
          </span>}
        </div>

        {loading ? (
          <div className="flex justify-center py-20">
            <span className="loading loading-spinner loading-lg"></span>
          </div>
        ) : filteredSanList.length === 0 ? (
          <div className="text-center py-20 text-gray-500">
            Không tìm thấy sân phù hợp
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            {filteredSanList.map((san) => (
              <SanCard key={san.id} san={san} />
            ))}
          </div>
        )}
      </div>
    </>
  );
}
