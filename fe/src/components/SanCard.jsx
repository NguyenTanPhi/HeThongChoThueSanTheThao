import { Link } from "react-router-dom";

export default function SanCard({ san }) {
  return (
    <Link to={`/san/${san.id}`}>
      <div className="card bg-white shadow-xl hover:shadow-2xl transition-all hover:-translate-y-1 rounded-xl overflow-hidden">
        <figure className="h-48 bg-gray-200">
          {san.hinh_anh ? (
            <img
              src={`http://localhost:8000/storage/${san.hinh_anh}`}
              alt={san.ten_san}
              className="w-full h-full object-cover"
            />
          ) : (
            <div className="bg-gray-300 w-full h-full flex items-center justify-center">
              <span className="text-gray-600 text-xl">Chưa có ảnh</span>
            </div>
          )}
        </figure>

        <div className="card-body p-5">
          <h3 className="card-title text-lg">{san.ten_san}</h3>

          {/* ❌ Bỏ phần đánh giá ở đây */}
          {san.loai_san && (
    <p className="mt-1 text-sm text-emerald-600 font-medium">
      {san.loai_san}
    </p>
  )}

          <p className="text-sm text-gray-600 mt-2 line-clamp-2">
            {san.dia_chi}
          </p>

          <div className="mt-4 flex justify-between items-center">
            <span className="text-2xl font-bold text-emerald-600">
              {Number(san.gia_thue).toLocaleString("vi-VN")}đ
            </span>
            <span className="text-sm text-gray-500">/giờ</span>
          </div>
{san.lich_trong && san.lich_trong.length > 0 && (
  <div className="mt-4 text-sm text-gray-700">
    <p className="font-semibold">Lịch trống gần nhất:</p>
    <ul className="list-disc list-inside">
      {san.lich_trong
        .filter((lich) => {
          // Tính thời gian bắt đầu và kết thúc
          const start = new Date(`${lich.ngay}T${lich.gio_bat_dau}`);
          const end = new Date(`${lich.ngay}T${lich.gio_ket_thuc}`);
          const now = new Date();

          // Khoảng thời gian (ms)
          const duration = end - start;
          const halfDuration = duration / 2;

          // Nếu hiện tại đã vượt quá nửa thời gian → bỏ
          return now.getTime() <= start.getTime() + halfDuration;
        })
        .slice(0, 2) // chỉ lấy 2 lịch gần nhất
        .map((lich) => (
          <li key={lich.id}>
            {lich.ngay} | {lich.gio_bat_dau} - {lich.gio_ket_thuc}
          </li>
        ))}
    </ul>
  </div>
)}

          <div className="card-actions mt-4">
            <button className="btn btn-success btn-sm w-full text-white">
              Xem chi tiết
            </button>
          </div>
        </div>
      </div>
    </Link>
  );
}
  