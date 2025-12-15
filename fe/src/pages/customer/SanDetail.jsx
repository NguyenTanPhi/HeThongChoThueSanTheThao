// src/pages/customer/SanDetail.jsx
import { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { axiosPublic } from "../../api/instance";

function getInitials(name) {
  return name
    .split(" ")
    .map((word) => word[0])
    .join("")
    .toUpperCase();
}

export default function SanDetail() {
  const { id } = useParams();
  const [san, setSan] = useState(null);
  const [danhGia, setDanhGia] = useState(null);
  const [lichTrong, setLichTrong] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedLich, setSelectedLich] = useState(null);
  const [isConfirmOpen, setIsConfirmOpen] = useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    axiosPublic.get(`/san/${id}`)
      .then(res => {
        setSan(res.data.data || res.data);
        setLoading(false);
      })
      .catch(() => setLoading(false));

    axiosPublic.get(`/danh-gia/san/${id}`)
      .then(res => setDanhGia(res.data))
      .catch(err => console.error(err));

    axiosPublic.get(`/san/${id}/lich-trong`)
      .then(res => setLichTrong(res.data))
      .catch(err => console.error(err));
  }, [id]);

  if (loading) return (
    <div className="flex justify-center py-20">
      <span className="loading loading-spinner loading-lg"></span>
    </div>
  );
  if (!san) return (
    <div className="text-center py-20 text-2xl">Không tìm thấy sân</div>
  );

  return (
    <div className="min-h-screen bg-gray-50 pt-20">
      <div className="container mx-auto px-4 py-8">
        <button
          onClick={() => navigate(-1)}
          className="mb-6 px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg"
        >
          ← Quay lại
        </button>

        <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div>
            <img 
              src={`http://localhost:8000/storage/${san.hinh_anh}`} 
              alt={san.ten_san}
              className="w-full h-96 object-cover rounded-2xl shadow-xl"
            />
          </div>
          <div>
            <h1 className="text-4xl font-bold mb-4">{san.ten_san}</h1>
            <p className="text-xl text-gray-600 mb-6">{san.dia_chi}</p>
            <div className="text-3xl font-bold text-success mb-8">
              {Number(san.gia_thue).toLocaleString("vi-VN")}đ / giờ
            </div>

            {/* Lịch trống */}
            <h2 className="text-2xl font-semibold mb-4">Lịch trống</h2>
            {lichTrong.length > 0 ? (
  <ul className="space-y-4">
    {lichTrong
      .filter((lich) => {
        const start = new Date(`${lich.ngay}T${lich.gio_bat_dau}`);
        const end = new Date(`${lich.ngay}T${lich.gio_ket_thuc}`);
        const now = new Date();

        const duration = end - start;
        const halfDuration = duration / 2;

        // chỉ giữ lịch nếu hiện tại chưa vượt quá nửa thời gian
        return now.getTime() <= start.getTime() + halfDuration;
      })
      .map((lich) => (
        <li
          key={lich.id}
          className="border p-3 rounded-lg flex justify-between items-center"
        >
          <div>
            <p className="font-medium">
              Ngày: {lich.ngay} | {lich.gio_bat_dau} - {lich.gio_ket_thuc}
            </p>
            <p className="text-gray-600">
              Giá: {Number(lich.gia).toLocaleString("vi-VN")}đ
            </p>
          </div>
          <button
            className="btn btn-success btn-sm"
            onClick={() => {
              setSelectedLich(lich);
              setIsConfirmOpen(true);
            }}
          >
            Đặt ngay
          </button>
        </li>
      ))}
  </ul>
) : (
  <p className="text-gray-600">Hiện chưa có lịch trống</p>
)}


            {/* Đánh giá */}
            {danhGia && (
              <div className="mt-8">
                <h2 className="text-2xl font-semibold mb-2">
                  Đánh giá trung bình: {danhGia.trung_binh} ⭐
                </h2>
                <p className="text-gray-600 mb-4">
                  Tổng số đánh giá: {danhGia.tong_so}
                </p>
                <ul className="space-y-4">
                  {danhGia.danh_gia.map((dg) => (
                    <li key={dg.id} className="p-4 bg-white rounded-lg shadow">
                      <div className="flex items-center gap-3 mb-2">
                        {dg.avatar ? (
                          <img
                            src={`http://localhost:8000/storage/${dg.avatar}`}
                            alt={dg.ten_nguoi_dung}
                            className="w-10 h-10 rounded-full object-cover"
                          />
                        ) : (
                          <div className="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white font-bold">
                            {getInitials(dg.ten_nguoi_dung)}
                          </div>
                        )}
                        <div>
                          <p className="font-semibold">{dg.ten_nguoi_dung}</p>
                          <div className="text-yellow-500">
                            {"⭐".repeat(dg.diem_danh_gia)}
                          </div>
                        </div>
                      </div>
                      <p className="text-gray-700">{dg.noi_dung}</p>
                    </li>
                  ))}
                </ul>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Modal xác nhận đặt sân */}
      {isConfirmOpen && selectedLich && (
        <div className="fixed inset-0 flex items-center justify-center z-50">
          <div className="absolute inset-0 bg-black bg-opacity-50" onClick={() => setIsConfirmOpen(false)}></div>
          <div className="bg-white rounded-xl shadow-lg p-6 z-10 w-full max-w-md">
            <h2 className="text-2xl font-bold mb-4">Xác nhận đặt sân</h2>
            <p className="mb-4">
              Bạn có chắc muốn đặt sân vào ngày <b>{selectedLich.ngay}</b> từ <b>{selectedLich.gio_bat_dau}</b> đến <b>{selectedLich.gio_ket_thuc}</b>?
            </p>
            <p className="mb-6 text-success font-semibold">
              Giá: {Number(selectedLich.gia).toLocaleString("vi-VN")}đ
            </p>
            <div className="flex justify-end gap-3">
              <button className="btn" onClick={() => setIsConfirmOpen(false)}>Hủy</button>
              <button 
  className="btn btn-success"
  onClick={() => {
    setIsConfirmOpen(false);
    navigate(
  `/thanh-toan?lich_id=${selectedLich.id}&ngay=${selectedLich.ngay}&gio_bat_dau=${selectedLich.gio_bat_dau}&gio_ket_thuc=${selectedLich.gio_ket_thuc}&gia=${selectedLich.gia}`
);

  }}
>
  Thanh toán
</button>


            </div>
          </div>
        </div>
      )}
    </div>
  );
}
