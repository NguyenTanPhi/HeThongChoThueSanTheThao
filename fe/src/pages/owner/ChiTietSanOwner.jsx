// src/pages/owner/ChiTietSanOwner.jsx
import { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { axiosPrivate, axiosPublic } from "../../api/instance";
import LichTrongCalendar from '../../components/LichTrongCalendar'; // điều chỉnh path nếu cần

export default function ChiTietSanOwner({ setActiveTab }) {
  const { id } = useParams();
  const navigate = useNavigate();

  const [san, setSan] = useState(null);
  const [danhGia, setDanhGia] = useState([]);
  const [trungBinh, setTrungBinh] = useState(0);
  const [tongSo, setTongSo] = useState(0);
  const [lichTrong, setLichTrong] = useState([]);
  const [loading, setLoading] = useState(true);

  const [toast, setToast] = useState(null);
  const showToast = (type, message) => {
    setToast({ type, message });
    setTimeout(() => setToast(null), 3000);
  };

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [sanRes, dgRes, lichRes] = await Promise.all([
          axiosPublic.get(`/san/${id}`),
          axiosPublic.get(`/danh-gia/san/${id}`),
          axiosPrivate.get(`/owner/san/${id}/lich-trong`)
        ]);

        setSan(sanRes.data.data || sanRes.data);

        const dgData = dgRes.data || {};
        setDanhGia(dgData.danh_gia || []);
        setTrungBinh(dgData.trung_binh || 0);
        setTongSo(dgData.tong_so || 0);

        setLichTrong(Array.isArray(lichRes.data) ? lichRes.data : []);
      } catch (err) {
        console.error("Lỗi tải dữ liệu:", err);

        if (err.response?.status === 403 && err.response.data?.require_package) {
          setToast({
            type: "error",
            message: err.response.data.message || "Gói dịch vụ chưa có hoặc đã hết hạn",
            action: () => setActiveTab("goi-dich-vu")
          });
        } else {
          showToast("error", "Lỗi tải dữ liệu");
        }
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [id, setActiveTab]);

  const confirmDeleteLich = async (lichId) => {
    try {
      await axiosPrivate.delete(`/owner/san/${id}/lich-trong/${lichId}`);
      setLichTrong(prev => prev.filter(lt => lt.id !== lichId));
      showToast("success", "Đã xóa lịch trống!");
    } catch (err) {
      showToast("error", "Xóa lịch thất bại!");
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center py-20">
        <span className="loading loading-spinner loading-lg"></span>
      </div>
    );
  }

  if (!san) {
    return <div className="text-center py-20 text-2xl">Không tìm thấy sân</div>;
  }

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
              src={`${san.hinh_anh}`}
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

            <div className="mt-8">
              <div className="flex justify-between items-center mb-4">
                <h2 className="text-2xl font-semibold">Lịch trống</h2>
              </div>

              <LichTrongCalendar
                lichTrong={lichTrong}
                onAddSlot={async (newSlot) => {
                  if (san.trang_thai_duyet !== "da_duyet") {
                    showToast("error", "Sân chưa được duyệt hoặc bị từ chối, không thể thêm lịch!");
                    return;
                  }

                  // Check thời gian tương lai
                  const now = new Date();
                  const lichStart = new Date(`${newSlot.ngay}T${newSlot.gio_bat_dau}`);
                  if (lichStart <= now) {
                    showToast("error", "Không thể thêm lịch đã qua");
                    return;
                  }

                  // Check overlap
                  const isOverlap = lichTrong.some(lt => {
                    if (lt.ngay !== newSlot.ngay) return false;
                    const existingStart = new Date(`${lt.ngay}T${lt.gio_bat_dau}`);
                    const existingEnd = new Date(`${lt.ngay}T${lt.gio_ket_thuc}`);
                    const newStart = new Date(`${newSlot.ngay}T${newSlot.gio_bat_dau}`);
                    const newEnd = new Date(`${newSlot.ngay}T${newSlot.gio_ket_thuc}`);
                    return newStart < existingEnd && newEnd > existingStart;
                  });

                  if (isOverlap) {
                    showToast("error", "Lịch mới bị trùng giờ với lịch đã có");
                    return;
                  }

                  const payload = { ...newSlot };

                  try {
                    const res = await axiosPrivate.post(`/owner/san/${id}/lich-trong`, payload);
                    if (res.data.success) {
                      setLichTrong(prev => [...prev, { id: res.data.id, ...payload }]);
                      showToast("success", "Thêm lịch trống thành công!");
                    }
                  } catch (err) {
                    if (err.response?.status === 403 && err.response.data?.require_package) {
                      setToast({
                        type: "error",
                        message: err.response.data.message || "Gói dịch vụ chưa có hoặc đã hết hạn",
                        action: () => setActiveTab("goi-dich-vu"),
                      });
                    } else {
                      showToast("error", "Thêm lịch thất bại. Kiểm tra dữ liệu!");
                    }
                  }
                }}  
                onDeleteSlot={confirmDeleteLich}
              />
            </div>

            <div className="mt-12">
              <h2 className="text-2xl font-semibold mb-4">
                Đánh giá sân ({tongSo} đánh giá, trung bình {trungBinh}⭐)
              </h2>

              {danhGia.length > 0 ? (
                <ul className="space-y-4">
                  {danhGia.map(dg => (
                    <li key={`dg-${dg.id}`} className="p-4 bg-white rounded-lg shadow">
                      <p className="font-semibold">{dg.ten_nguoi_dung}</p>
                      <div className="text-yellow-500">
                        {"⭐".repeat(dg.diem_danh_gia)}
                      </div>
                      <p className="text-gray-700">{dg.noi_dung}</p>
                    </li>
                  ))}
                </ul>
              ) : (
                <p className="text-gray-600">Chưa có đánh giá</p>
              )}
            </div>
          </div>
        </div>
      </div>

      {/* Toast */}
      {toast && (
        <div
          className={`fixed bottom-5 right-5 px-6 py-3 rounded-lg shadow-xl text-white font-medium transition-all ${
            toast.type === "success" ? "bg-green-600" : "bg-red-600"
          } flex items-center justify-between`}
        >
          <span>{toast.message}</span>
          {toast.action && (
            <button
              onClick={() => {
                  setToast(null);
                  setActiveTab("goi-dich-vu");
                }}
              className="ml-4 bg-white text-red-600 px-3 py-1 rounded hover:bg-gray-200"
              
            >
              Mua gói ngay
            </button>
          )}
        </div>
      )}
    </div>
  );
}