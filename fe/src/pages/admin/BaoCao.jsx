import { useEffect, useState, useRef } from "react";
import html2pdf from "html2pdf.js";
import { axiosPrivate } from "../../api/instance";
import { Bar } from "react-chartjs-2";
import {
  Chart as ChartJS,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
} from "chart.js";
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";

ChartJS.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend);

export default function BaoCao() {
  const reportRef = useRef();
  const [datSan, setDatSan] = useState([]);
  const [goiDichVu, setGoiDichVu] = useState([]);
  const [loading, setLoading] = useState(true);
  const [exporting, setExporting] = useState(false);

  // Tách riêng 2 state string → tránh re-render vô hạn
  const [fromDate, setFromDate] = useState("");
  const [toDate, setToDate] = useState("");

  const tongDoanhThuDatSan = datSan.reduce(
    (total, item) => total + Number(item.so_tien || 0),
    0
  );

  const tongDoanhThuGoi = goiDichVu.reduce(
    (total, item) => total + Number(item.gia || 0),
    0
  );

  const tongDoanhThuHeThong = tongDoanhThuDatSan + tongDoanhThuGoi;

  useEffect(() => {
    fetchReports();
  }, [fromDate, toDate]); // Dependency là string → ổn định, không lặp

  const fetchReports = async () => {
    try {
      setLoading(true);
      const [resDatSan, resGoi] = await Promise.all([
        axiosPrivate.get("/admin/bao-cao/dat-san", {
          params: { from: fromDate, to: toDate },
        }),
        axiosPrivate.get("/admin/bao-cao/goi-dich-vu", {
          params: { from: fromDate, to: toDate },
        }),
      ]);

      setDatSan(resDatSan.data || []);
      setGoiDichVu(resGoi.data || []);
    } catch (err) {
      console.error(err);
      showToast("Không thể tải dữ liệu báo cáo!", "error");
    } finally {
      setLoading(false);
    }
  };

  const chartData = {
    labels: ["Đặt sân", "Gói dịch vụ", "Tổng hệ thống"],
    datasets: [
      {
        label: "Doanh thu (VNĐ)",
        data: [tongDoanhThuDatSan, tongDoanhThuGoi, tongDoanhThuHeThong],
        backgroundColor: ["#34d399", "#60a5fa", "#fbbf24"],
        borderRadius: 8,
      },
    ],
  };

  const exportExcel = async () => {
    if (exporting) return;
    setExporting(true);
    try {
      const worksheet = XLSX.utils.json_to_sheet([
        {
          "Doanh thu đặt sân": tongDoanhThuDatSan.toLocaleString("vi-VN") + "đ",
          "Doanh thu gói dịch vụ": tongDoanhThuGoi.toLocaleString("vi-VN") + "đ",
          "Tổng doanh thu hệ thống": tongDoanhThuHeThong.toLocaleString("vi-VN") + "đ",
        },
      ]);

      const workbook = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(workbook, worksheet, "BaoCao");

      const excelBuffer = XLSX.write(workbook, { bookType: "xlsx", type: "array" });
      const file = new Blob([excelBuffer], { type: "application/octet-stream" });

      const today = new Date().toLocaleDateString("vi-VN").replace(/\//g, "-");
      saveAs(file, `bao_cao_he_thong_${today}.xlsx`);
      showToast("Đã xuất file Excel thành công!", "success");
    } catch (err) {
      showToast("Lỗi khi xuất Excel!", "error");
    } finally {
      setExporting(false);
    }
  };

  const exportPDF = async () => {
    if (exporting) return;
    setExporting(true);
    try {
      const element = reportRef.current;
      const options = {
        margin: 10,
        filename: `bao_cao_he_thong_${new Date().toLocaleDateString("vi-VN").replace(/\//g, "-")}.pdf`,
        html2canvas: { scale: 2, useCORS: true },
        jsPDF: { unit: "mm", format: "a4", orientation: "portrait" },
      };

      await html2pdf().from(element).set(options).save();
      showToast("Đã xuất file PDF thành công!", "success");
    } catch (err) {
      showToast("Lỗi khi xuất PDF!", "error");
    } finally {
      setExporting(false);
    }
  };

  return (
    <div className="p-6">
      <ToastContainer />

      <h1 className="text-3xl font-bold mb-8 text-gray-800 tracking-tight">
        📊 Báo cáo thống kê hệ thống
      </h1>

      {/* Bộ lọc thời gian */}
      <div className="flex flex-col sm:flex-row gap-4 mb-8">
        <div className="flex-1">
          <label className="block text-sm font-medium mb-1">Từ ngày</label>
          <input
            type="date"
            className="input input-bordered w-full"
            value={fromDate}
            onChange={(e) => setFromDate(e.target.value)}
          />
        </div>
        <div className="flex-1">
          <label className="block text-sm font-medium mb-1">Đến ngày</label>
          <input
            type="date"
            className="input input-bordered w-full"
            value={toDate}
            onChange={(e) => setToDate(e.target.value)}
          />
        </div>
        <div className="flex items-end">
          <button
            className="btn btn-primary px-6"
            onClick={fetchReports}
            disabled={loading}
          >
            {loading ? (
              <span className="loading loading-spinner loading-sm"></span>
            ) : (
              "Lọc"
            )}
          </button>
        </div>
      </div>

      {/* Nội dung báo cáo */}
      {loading ? (
        <div className="flex flex-col items-center justify-center py-20 bg-white rounded-2xl shadow">
          <span className="loading loading-spinner loading-lg text-primary mb-4"></span>
          <p className="text-lg text-gray-600 font-medium">Đang tải dữ liệu báo cáo...</p>
        </div>
      ) : (
        <div ref={reportRef} id="pdf-content" className="space-y-8">
          {/* DOANH THU */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <RevenueCard title="Doanh thu đặt sân" value={tongDoanhThuDatSan} color="green" />
            <RevenueCard title="Doanh thu gói dịch vụ" value={tongDoanhThuGoi} color="blue" />
            <RevenueCard title="Tổng doanh thu toàn hệ thống" value={tongDoanhThuHeThong} color="yellow" />
          </div>

          {/* BIỂU ĐỒ */}
          <div className="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
            <h2 className="text-xl font-bold mb-4 text-gray-800">📈 Biểu đồ doanh thu</h2>
            <div style={{ height: "400px" }}>
              <Bar data={chartData} options={{ responsive: true, maintainAspectRatio: false }} />
            </div>
          </div>

          {/* BÁO CÁO ĐẶT SÂN */}
          <ReportTable
            title="📌 Báo cáo đặt sân"
            headers={["Sân", "Khách hàng", "Ngày đặt", "Giờ", "Giá"]}
            emptyText="Không có dữ liệu đặt sân trong khoảng thời gian này"
            rows={datSan.map((item) => [
              item.ten_san || "N/A",
              item.nguoi_dat || "N/A",
              item.ngay_dat || "N/A",
              `${item.gio_bat_dau || "--"} - ${item.gio_ket_thuc || "--"}`,
              Number(item.so_tien || 0).toLocaleString("vi-VN") + "đ",
            ])}
          />

          {/* BÁO CÁO GÓI DỊCH VỤ */}
          <ReportTable
            title="📦 Báo cáo gói dịch vụ"
            headers={["Chủ sân", "Gói", "Giá", "Ngày mua", "Ngày hết hạn"]}
            emptyText="Không có dữ liệu gói dịch vụ trong khoảng thời gian này"
            rows={goiDichVu.map((item) => [
              item.nguoi_dung || "N/A",
              item.ten_goi || "N/A",
              Number(item.gia || 0).toLocaleString("vi-VN") + "đ",
              item.ngay_mua || "N/A",
              item.ngay_het || "N/A",
            ])}
          />
        </div>
      )}

      {/* BUTTON EXPORT */}
      <div className="flex flex-col sm:flex-row gap-4 mt-8">
        <button
          className="px-5 py-3 rounded-xl bg-green-600 text-white font-medium hover:bg-green-700 transition shadow flex-1 flex items-center justify-center gap-2 min-w-[180px]"
          onClick={exportExcel}
          disabled={loading || exporting}
        >
          {exporting ? (
            <>
              <span className="loading loading-spinner loading-sm"></span>
              Đang xuất...
            </>
          ) : (
            <>📥 Xuất Excel</>
          )}
        </button>

        <button
          className="px-5 py-3 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 transition shadow flex-1 flex items-center justify-center gap-2 min-w-[180px]"
          onClick={exportPDF}
          disabled={loading || exporting}
        >
          {exporting ? (
            <>
              <span className="loading loading-spinner loading-sm"></span>
              Đang xuất...
            </>
          ) : (
            <>📄 Xuất PDF</>
          )}
        </button>
      </div>
    </div>
  );
}

/* Các component phụ giữ nguyên như cũ: RevenueCard, ReportTable, ToastContainer, showToast */

/* ✅ CARD DOANH THU */
function RevenueCard({ title, value, color }) {
  const colors = {
    green: "bg-green-50 text-green-700 border-green-200",
    blue: "bg-blue-50 text-blue-700 border-blue-200",
    yellow: "bg-yellow-50 text-yellow-700 border-yellow-200",
  };

  return (
    <div
      className={`p-6 rounded-2xl shadow-md border ${colors[color]} transition hover:shadow-xl`}
    >
      <p className="font-semibold text-gray-700">{title}</p>
      <p className="text-2xl font-bold mt-2">{value.toLocaleString("vi-VN")}đ</p>
    </div>
  );
}

/* ✅ BẢNG BÁO CÁO */
function ReportTable({ title, headers, rows, emptyText }) {
  return (
    <div className="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
      <h2 className="text-xl font-bold mb-4 text-gray-800">{title}</h2>

      <div className="overflow-x-auto">
        <table className="table table-zebra w-full">
          <thead className="bg-gray-50 text-gray-700 font-semibold">
            <tr>
              {headers.map((h, i) => (
                <th key={i}>{h}</th>
              ))}
            </tr>
          </thead>

          <tbody>
            {rows.length === 0 ? (
              <tr>
                <td colSpan={headers.length} className="text-center py-10 text-gray-500">
                  {emptyText}
                </td>
              </tr>
            ) : (
              rows.map((row, i) => (
                <tr key={i} className="hover:bg-gray-50 transition">
                  {row.map((cell, j) => (
                    <td key={j}>{cell}</td>
                  ))}
                </tr>
              ))
            )}
          </tbody>
        </table>
      </div>
    </div>
  );
}

/* ✅ TOAST */
function ToastContainer() {
  if (!window.toast) {
    window.toast = {
      show: (msg, type = "info") => {
        const div = document.createElement("div");

        const colors = {
          success: "bg-green-500",
          error: "bg-red-500",
          warning: "bg-yellow-500",
          info: "bg-blue-500",
        };

        const icons = {
          success: "✅",
          error: "❌",
          warning: "⚠️",
          info: "ℹ️",
        };

        div.className = `
          flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl text-white
          ${colors[type] || colors.info}
          animate-slide-in
          mb-3
        `;

        div.innerHTML = `
          <span class="text-xl">${icons[type]}</span>
          <span class="font-medium">${msg}</span>
        `;

        const root = document.getElementById("toast-root");
        root.appendChild(div);

        setTimeout(() => {
          div.classList.add("animate-slide-out");
          setTimeout(() => div.remove(), 300);
        }, 3500);
      },
    };
  }

  return (
    <div id="toast-root" className="fixed top-5 right-5 z-[9999] flex flex-col items-end gap-2"></div>
  );
}

function showToast(msg, type) {
  window.toast.show(msg, type);
}