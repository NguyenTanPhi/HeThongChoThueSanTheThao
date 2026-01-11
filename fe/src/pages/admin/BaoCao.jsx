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

const LIMIT = 5;

export default function BaoCao() {
  const reportRef = useRef();

  const [datSan, setDatSan] = useState([]);
  const [goiDichVu, setGoiDichVu] = useState([]);
  const [loading, setLoading] = useState(true);
  const [exporting, setExporting] = useState(false);

  /* ===== FILTER ===== */
  const [filterType, setFilterType] = useState("range"); // range | month | year
  const [fromDate, setFromDate] = useState("");
  const [toDate, setToDate] = useState("");
  const [month, setMonth] = useState("");
  const [year, setYear] = useState("");

  /* ===== PAGINATION (FE) ===== */
  const [pageDatSan, setPageDatSan] = useState(1);
  const [pageGoi, setPageGoi] = useState(1);

  useEffect(() => {
    fetchReports();
  }, []);

  const fetchReports = async () => {
    try {
      setLoading(true);
      setPageDatSan(1);
      setPageGoi(1);

      let params = {};

      if (filterType === "range") {
        params.from = fromDate;
        params.to = toDate;
      }

      if (filterType === "month") {
        params.month = month;
        params.year = year;
      }

      if (filterType === "year") {
        params.year = year;
      }

      const [resDatSan, resGoi] = await Promise.all([
        axiosPrivate.get("/admin/bao-cao/dat-san", { params }),
        axiosPrivate.get("/admin/bao-cao/goi-dich-vu", { params }),
      ]);

      setDatSan(resDatSan.data || []);
      setGoiDichVu(resGoi.data || []);
    } catch (err) {
      showToast("Không thể tải dữ liệu báo cáo", "error");
    } finally {
      setLoading(false);
    }
  };

  /* ===== DOANH THU ===== */
  const tongDatSan = datSan.reduce((t, i) => t + Number(i.so_tien || 0), 0);
  const tongGoi = goiDichVu.reduce((t, i) => t + Number(i.gia || 0), 0);
  const tongHeThong = tongDatSan + tongGoi;

  const chartData = {
    labels: ["Đặt sân", "Gói dịch vụ", "Tổng"],
    datasets: [
      {
        label: "Doanh thu (VNĐ)",
        data: [tongDatSan, tongGoi, tongHeThong],
        backgroundColor: ["#22c55e", "#3b82f6", "#f59e0b"],
        borderRadius: 8,
      },
    ],
  };

  /* ===== EXPORT ===== */
  const exportExcel = async () => {
    if (exporting) return;
    setExporting(true);

    const ws = XLSX.utils.json_to_sheet([
      {
        "Doanh thu đặt sân": tongDatSan,
        "Doanh thu gói dịch vụ": tongGoi,
        "Tổng doanh thu": tongHeThong,
      },
    ]);

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "BaoCao");

    const buffer = XLSX.write(wb, { bookType: "xlsx", type: "array" });
    saveAs(new Blob([buffer]), "bao_cao_he_thong.xlsx");

    setExporting(false);
    showToast("Xuất Excel thành công", "success");
  };

  const exportPDF = async () => {
    if (exporting) return;
    setExporting(true);

    await html2pdf()
      .from(reportRef.current)
      .set({
        margin: 10,
        filename: "bao_cao_he_thong.pdf",
        html2canvas: { scale: 2 },
      })
      .save();

    setExporting(false);
    showToast("Xuất PDF thành công", "success");
  };

  return (
    <div className="p-6">
      <ToastContainer />

      <h1 className="text-3xl font-bold mb-6">📊 Báo cáo hệ thống</h1>

      {/* ===== FILTER ===== */}
      <div className="flex gap-4 flex-wrap mb-6">
        <select
          className="select select-bordered"
          value={filterType}
          onChange={(e) => setFilterType(e.target.value)}
        >
          <option value="range">Khoảng ngày</option>
          <option value="month">Theo tháng</option>
          <option value="year">Theo năm</option>
        </select>

        {filterType === "range" && (
          <>
            <input type="date" className="input input-bordered"
              value={fromDate} onChange={(e) => setFromDate(e.target.value)} />
            <input type="date" className="input input-bordered"
              value={toDate} onChange={(e) => setToDate(e.target.value)} />
          </>
        )}

        {filterType === "month" && (
          <input type="month" className="input input-bordered"
            onChange={(e) => {
              setYear(e.target.value.slice(0, 4));
              setMonth(e.target.value.slice(5, 7));
            }}
          />
        )}

        {filterType === "year" && (
          <input type="number" placeholder="Năm"
            className="input input-bordered"
            value={year} onChange={(e) => setYear(e.target.value)} />
        )}

        <button className="btn btn-primary" onClick={fetchReports}>
          Lọc
        </button>
      </div>

      {loading ? (
        <p>Đang tải...</p>
      ) : (
        <div ref={reportRef} className="space-y-8">
          {/* ===== CARD ===== */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <RevenueCard title="Đặt sân" value={tongDatSan} />
            <RevenueCard title="Gói dịch vụ" value={tongGoi} />
            <RevenueCard title="Tổng hệ thống" value={tongHeThong} />
          </div>

          {/* ===== CHART ===== */}
          <div className="bg-white p-6 rounded-xl shadow">
            <Bar data={chartData} />
          </div>

          {/* ===== DAT SAN ===== */}
          <ReportTable
            title="📌 Báo cáo đặt sân"
            headers={["Sân", "Khách", "Ngày", "Giờ", "Giá"]}
            rows={datSan
              .slice(0, pageDatSan * LIMIT)
              .map((i) => [
                i.ten_san,
                i.nguoi_dat,
                i.ngay_dat,
                `${i.gio_bat_dau} - ${i.gio_ket_thuc}`,
                Number(i.so_tien).toLocaleString("vi-VN") + "đ",
              ])}
          />

          {datSan.length > pageDatSan * LIMIT && (
            <button className="btn btn-outline"
              onClick={() => setPageDatSan(pageDatSan + 1)}>
              Xem thêm đặt sân
            </button>
          )}

          {/* ===== GOI DICH VU ===== */}
          <ReportTable
            title="📦 Báo cáo gói dịch vụ"
            headers={["Người dùng", "Gói", "Giá", "Ngày mua", "Ngày hết"]}
            rows={goiDichVu
              .slice(0, pageGoi * LIMIT)
              .map((i) => [
                i.nguoi_dung,
                i.ten_goi,
                Number(i.gia).toLocaleString("vi-VN") + "đ",
                i.ngay_mua,
                i.ngay_het,
              ])}
          />

          {goiDichVu.length > pageGoi * LIMIT && (
            <button className="btn btn-outline"
              onClick={() => setPageGoi(pageGoi + 1)}>
              Xem thêm gói dịch vụ
            </button>
          )}
        </div>
      )}

      {/* ===== EXPORT ===== */}
      <div className="flex gap-4 mt-8">
        <button className="btn btn-success" onClick={exportExcel}>Xuất Excel</button>
        <button className="btn btn-info" onClick={exportPDF}>Xuất PDF</button>
      </div>
    </div>
  );
}

/* ===== COMPONENTS ===== */

function RevenueCard({ title, value }) {
  return (
    <div className="bg-white p-6 rounded-xl shadow">
      <p className="font-semibold">{title}</p>
      <p className="text-2xl font-bold">
        {value.toLocaleString("vi-VN")}đ
      </p>
    </div>
  );
}

function ReportTable({ title, headers, rows }) {
  return (
    <div className="bg-white p-6 rounded-xl shadow">
      <h2 className="font-bold mb-4">{title}</h2>
      <table className="table table-zebra w-full">
        <thead>
          <tr>{headers.map((h, i) => <th key={i}>{h}</th>)}</tr>
        </thead>
        <tbody>
          {rows.length === 0 ? (
            <tr><td colSpan={headers.length} className="text-center">Không có dữ liệu</td></tr>
          ) : (
            rows.map((r, i) => (
              <tr key={i}>{r.map((c, j) => <td key={j}>{c}</td>)}</tr>
            ))
          )}
        </tbody>
      </table>
    </div>
  );
}

/* ===== TOAST ===== */
function ToastContainer() {
  if (!window.toast) {
    window.toast = {
      show: (msg, type = "info") => alert(msg),
    };
  }
  return null;
}

function showToast(msg, type) {
  window.toast.show(msg, type);
}
