import React, { useEffect, useState } from "react";
import { getSanList } from "../../api/san.js";
import SanCard from "../../components/SanCard.jsx";
import Header from "../../components/Header";
import { axiosPublic } from "../../api/instance";
import { useNavigate } from "react-router-dom";

export default function Home() {
  const [sanList, setSanList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState("");
  const navigate = useNavigate();

  useEffect(() => {
  getSanList()
    .then((res) => {
      // Laravel paginate → data nằm trong data
      const listSan = res.data.data || [];
      setSanList(listSan);
      setLoading(false);
    })
    .catch(() => setLoading(false));
}, []);


  const filteredSanList = sanList.filter((san) =>
    san.ten_san.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <>
      <Header />

      {/* HERO */}
      <div className="relative h-[480px] flex items-center justify-center overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-br from-green-600 to-emerald-800 opacity-95"></div>
        <div className="absolute inset-0 bg-[url('https://heropatterns.com/patterns/microbial-mat.png')] opacity-15"></div>

        <div className="relative z-10 text-center text-white px-4">
          <h1 className="text-6xl md:text-7xl font-bold mb-6 drop-shadow-xl">
            Đặt Sân Bóng Nhanh Chóng
          </h1>

          <p className="text-2xl md:text-3xl font-medium mb-10 opacity-95">
            Tìm sân – đặt sân – dễ dàng chỉ với vài thao tác!
          </p>

          <div className="flex flex-wrap justify-center gap-4">
            <a
              href="#danh-sach-san"
              className="btn bg-white text-green-700 btn-lg shadow-lg hover:bg-gray-200 border-none"
            >
              🔍 Tìm sân ngay
            </a>

            <button
              onClick={() => navigate("/lich-su-dat")}
              className="btn btn-warning btn-lg shadow-lg"
            >
              📖 Lịch sử đặt sân
            </button>

            <button
              onClick={() => navigate("/tai-khoan")}
              className="btn btn-success btn-lg text-white shadow-lg"
            >
              👤 Tài khoản
            </button>
          </div>
        </div>
      </div>

      {/* DANH SÁCH SÂN */}
      <div id="danh-sach-san" className="container mx-auto px-5 py-16 bg-gray-50">
        <h2 className="text-4xl font-bold text-center mb-10 text-gray-800">
          Các sân bóng nổi bật
        </h2>

        {/* SEARCH BOX */}
        <div className="flex justify-center mb-10">
          <div className="relative w-full max-w-md">
            <span className="absolute inset-y-0 left-4 flex items-center text-gray-400 text-xl">
              🔍
            </span>
            <input
              type="text"
              placeholder="Nhập tên sân để tìm..."
              className="input input-bordered pl-12 w-full rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-green-600"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
          </div>
        </div>

        {loading ? (
          <div className="flex justify-center py-20">
            <span className="loading loading-spinner loading-lg"></span>
          </div>
        ) : filteredSanList.length === 0 ? (
          <div className="text-center py-20 text-xl text-gray-500">
            Không tìm thấy sân nào phù hợp
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            {filteredSanList.map((san) => (
              <SanCard key={san.id} san={san} />
            ))}
          </div>
        )}
      </div>
    </>
  );
}
