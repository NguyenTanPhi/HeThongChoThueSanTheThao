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

  const [filter, setFilter] = useState({
    from: "",
    to: "",
  });

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
  }, [filter]);

  const fetchReports = async () => {
    try {
      const resDatSan = await axiosPrivate.get("/admin/bao-cao/dat-san", {
        params: filter,
      });

      const resGoi = await axiosPrivate.get("/admin/bao-cao/goi-dich-vu", {
        params: filter,
      });

      setDatSan(resDatSan.data);
      setGoiDichVu(resGoi.data);
    } catch (err) {
      showToast("Không thể tải báo cáo!", "error");
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

  const exportExcel = () => {
    const worksheet = XLSX.utils.json_to_sheet([
      {
        "Doanh thu đặt sân": tongDoanhThuDatSan,
        "Doanh thu gói dịch vụ": tongDoanhThuGoi,
        "Tổng doanh thu hệ thống": tongDoanhThuHeThong,
      },
    ]);

    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "BaoCao");

    const excelBuffer = XLSX.write(workbook, {
      bookType: "xlsx",
      type: "array",
    });

    const file = new Blob([excelBuffer], {
      type: "application/octet-stream",
    });

    saveAs(file, "bao_cao_he_thong.xlsx");
  };

  const exportPDF = () => {
    const element = reportRef.current;

    const options = {
      margin: 10,
      filename: "bao_cao_he_thong.pdf",
      html2canvas: {
        scale: 2,
        useCORS: true,
      },
      jsPDF: { unit: "mm", format: "a4", orientation: "portrait" },
    };

    html2pdf().from(element).set(options).save();
  };

  return (
    <div className="p-6">
      <ToastContainer />

      <h1 className="text-3xl font-bold mb-8 text-gray-800 tracking-tight">
        📊 Báo cáo thống kê hệ thống
      </h1>

      {/* VÙNG XUẤT PDF */}
      <div ref={reportRef} id="pdf-content" className="space-y-8">

        {/* ======= DOANH THU ======= */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          <RevenueCard
            title="Doanh thu đặt sân"
            value={tongDoanhThuDatSan}
            color="green"
          />
          <RevenueCard
            title="Doanh thu gói dịch vụ"
            value={tongDoanhThuGoi}
            color="blue"
          />
          <RevenueCard
            title="Tổng doanh thu toàn hệ thống"
            value={tongDoanhThuHeThong}
            color="yellow"
          />
        </div>

        {/* ===== BIỂU ĐỒ ===== */}
        <div className="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
          <h2 className="text-xl font-bold mb-4 text-gray-800">
            📈 Biểu đồ doanh thu
          </h2>
          <Bar data={chartData} />
        </div>

        {/* ======= BÁO CÁO ĐẶT SÂN ======= */}
        <ReportTable
          title="📌 Báo cáo đặt sân"
          headers={["Sân", "Khách hàng", "Ngày đặt", "Giờ", "Giá"]}
          emptyText="Không có dữ liệu đặt sân"
          rows={datSan.map((item) => [
            item.ten_san,
            item.nguoi_dat,
            item.ngay_dat,
            `${item.gio_bat_dau} - ${item.gio_ket_thuc}`,
            Number(item.so_tien).toLocaleString() + "đ",
          ])}
        />

        {/* ======= BÁO CÁO GÓI DỊCH VỤ ======= */}
        <ReportTable
          title="📦 Báo cáo gói dịch vụ"
          headers={["Chủ sân", "Gói", "Giá", "Ngày mua", "Ngày hết hạn"]}
          emptyText="Không có dữ liệu gói dịch vụ"
          rows={goiDichVu.map((item) => [
            item.nguoi_dung,
            item.ten_goi,
            Number(item.gia).toLocaleString() + "đ",
            item.ngay_mua,
            item.ngay_het,
          ])}
        />
      </div>

      {/* BUTTON EXPORT */}
      <div className="flex gap-4 mt-8">
        <button
          className="px-5 py-3 rounded-xl bg-green-600 text-white font-medium hover:bg-green-700 transition shadow"
          onClick={exportExcel}
        >
          📥 Xuất Excel
        </button>

        <button
          className="px-5 py-3 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 transition shadow"
          onClick={exportPDF}
        >
          📄 Xuất PDF
        </button>
      </div>
    </div>
  );
}

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
      <p className="text-2xl font-bold mt-2">{value.toLocaleString()}đ</p>
    </div>
  );
}

/* ✅ BẢNG BÁO CÁO */
function ReportTable({ title, headers, rows, emptyText }) {
  return (
    <div className="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
      <h2 className="text-xl font-bold mb-4 text-gray-800">{title}</h2>

      <table className="table table-zebra">
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
              <td colSpan={headers.length} className="text-center py-4 text-gray-500">
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
        }, 2500);
      },
    };
  }

  return (
    <div id="toast-root" className="fixed top-5 right-5 z-[9999] flex flex-col items-end"></div>
  );
}

function showToast(msg, type) {
  window.toast.show(msg, type);
}
