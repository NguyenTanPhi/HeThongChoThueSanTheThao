// src/pages/owner/GoiDichVu.jsx
import React, { useEffect, useState } from "react";
import { axiosPrivate } from "../../api/instance";
import { useNavigate } from "react-router-dom";

export default function GoiDichVu() {
  const [goiHienTai, setGoiHienTai] = useState(null);
  const [goiList, setGoiList] = useState([]);
  const [loading, setLoading] = useState(true); // Thêm trạng thái loading
  const [modalData, setModalData] = useState(null);
  const coGoiConHan = goiHienTai?.trang_thai === "con_han";

  const navigate = useNavigate();

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);
        const [resCurrent, resList] = await Promise.all([
          axiosPrivate.get("/owner/goi-hien-tai"),
          axiosPrivate.get("/goi-dich-vu"),
        ]);

        setGoiHienTai(resCurrent.data || null);
        const list = Array.isArray(resList.data)
          ? resList.data
          : Array.isArray(resList.data?.data)
          ? resList.data.data
          : [];
        setGoiList(list);
      } catch (err) {
        console.error("Lỗi tải gói dịch vụ:", err);
        // Thay alert bằng toast nếu bạn có component toast, tạm dùng console
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, []);

  const getNgayHet = (goi) =>
    goi?.ngay_het || goi?.ngay_het_han || goi?.expire_date || goi?.end_date || null;

  const tinhThoiHanConLai = (ngayKetThuc) => {
    if (!ngayKetThuc) return null;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const end = new Date(ngayKetThuc);
    const diffTime = end - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays > 0 ? diffDays : 0;
  };

  const renderThoiHan = (ngayKetThuc) => {
    const days = tinhThoiHanConLai(ngayKetThuc);
    if (days === null) return <span className="text-gray-500">Không xác định</span>;
    if (days > 7) return <span className="text-green-600 font-semibold">{days} ngày</span>;
    if (days > 0) return <span className="text-yellow-600 font-semibold">{days} ngày (sắp hết)</span>;
    return <span className="text-red-600 font-semibold">Đã hết hạn</span>;
  };

  const muaGoi = (goiId, tenGoi, gia) => setModalData({ goiId, tenGoi, gia });

  const confirmMuaGoi = async () => {
    if (!modalData) return;
    try {
      const res = await axiosPrivate.post("/owner/thanh-toan", {
        goi_dich_vu_id: modalData.goiId,
      });
      if (res.data.payment_url) {
        navigate("/owner/thanh-toan", {
          state: {
            paymentUrl: res.data.payment_url,
            goiId: modalData.goiId,
            tenGoi: modalData.tenGoi,
            gia: modalData.gia,
          },
        });
      } else {
        alert(res.data.message || "Không tạo được đơn hàng!");
      }
    } catch (err) {
      console.error(err);
      alert("Lỗi kết nối server!");
    }
    setModalData(null);
  };

  return (
    <div className="min-h-screen bg-gray-50 py-8 px-4">
      <div className="max-w-6xl mx-auto">
        <h1 className="text-4xl font-bold text-center text-primary mb-8">Quản lý gói dịch vụ</h1>

        {loading ? (
          <div className="flex flex-col items-center justify-center py-20">
            <span className="loading loading-spinner loading-lg text-primary mb-4"></span>
            <p className="text-lg text-gray-600 font-medium">Đang tải thông tin gói dịch vụ...</p>
          </div>
        ) : (
          <>
            {/* Gói hiện tại */}
            {goiHienTai && goiHienTai.ten_goi && goiHienTai.trang_thai === "con_han" ? (
              <div className="relative bg-gradient-to-r from-blue-600 to-blue-800 text-white p-6 rounded-xl shadow-xl mb-8 overflow-hidden">
                <div className="absolute top-3 right-3 bg-yellow-400 text-black px-3 py-1 rounded-full font-semibold text-sm">
                  Đang dùng
                </div>
                <h2 className="text-xl font-semibold mb-2 opacity-90">Gói hiện tại</h2>
                <div className="text-4xl font-extrabold mb-2">{goiHienTai.ten_goi}</div>
                <div className="text-2xl mt-2">
                  Thời hạn còn lại: {renderThoiHan(getNgayHet(goiHienTai))}
                </div>
                <p className="text-sm opacity-80 mt-1">
                  Hết hạn: {getNgayHet(goiHienTai) ? new Date(getNgayHet(goiHienTai)).toLocaleDateString("vi-VN") : "Không xác định"}
                </p>
              </div>
            ) : (
              <div className="bg-yellow-100 border-l-4 border-yellow-400 text-yellow-800 p-5 rounded-xl text-center mb-8">
                <p className="text-xl font-semibold">Bạn chưa có gói dịch vụ</p>
                <p className="text-gray-600">Hãy chọn một gói bên dưới để kích hoạt ưu đãi!</p>
              </div>
            )}

            {/* Danh sách gói */}
            {!coGoiConHan && (
              <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                {goiList.length === 0 ? (
                  <div className="col-span-full text-center py-10 text-gray-500">
                    Chưa có gói dịch vụ nào
                  </div>
                ) : (
                  goiList.map((goi) => {
                    const laGoiHienTai = goiHienTai?.goi_id === goi.id;
                    return (
                      <div
                        key={goi.id}
                        className="relative bg-white rounded-xl shadow hover:shadow-lg transition p-5 flex flex-col justify-between"
                      >
                        {laGoiHienTai && (
                          <div className="absolute top-3 right-3 bg-blue-600 text-white px-2 py-0.5 rounded-full text-xs font-semibold">
                            Đang dùng
                          </div>
                        )}
                        <div>
                          <h3 className="text-lg font-bold text-primary mb-1">{goi.ten_goi}</h3>
                          <p className="text-green-600 font-semibold text-xl mb-1">
                            {Number(goi.gia).toLocaleString("vi-VN")} ₫
                          </p>
                          <p className="text-gray-500 text-sm mb-1 min-h-[40px]">{goi.mo_ta}</p>
                          <p className="text-gray-500 text-sm">Thời hạn: {goi.thoi_han} ngày</p>
                        </div>
                        <button
                          className={`btn w-full mt-3 ${laGoiHienTai ? "btn-outline-primary" : "btn-primary"}`}
                          onClick={() => muaGoi(goi.id, goi.ten_goi, goi.gia)}
                        >
                          {laGoiHienTai ? "Gia hạn" : "Mua"}
                        </button>
                      </div>
                    );
                  })
                )}
              </div>
            )}

            {/* Modal xác nhận mua/gia hạn */}
            {modalData && (
              <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4">
                <div className="bg-white rounded-xl shadow-xl p-6 max-w-sm w-full text-center">
                  <h3 className="text-xl font-bold mb-4 text-primary">
                    Xác nhận {goiHienTai?.goi_id === modalData.goiId ? "gia hạn" : "mua"} gói
                  </h3>
                  <p className="mb-2">
                    Tên gói: <strong>{modalData.tenGoi}</strong>
                  </p>
                  <p className="mb-4">
                    Giá: <strong>{Number(modalData.gia).toLocaleString("vi-VN")} ₫</strong>
                  </p>
                  <div className="flex gap-4 justify-center">
                    <button className="btn btn-secondary" onClick={() => setModalData(null)}>
                      Hủy
                    </button>
                    <button className="btn btn-primary" onClick={confirmMuaGoi}>
                      Xác nhận
                    </button>
                  </div>
                </div>
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
}