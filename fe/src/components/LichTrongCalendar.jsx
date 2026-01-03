import { useMemo, useState } from 'react';
import { Calendar, dateFnsLocalizer } from 'react-big-calendar';
import format from 'date-fns/format';
import parse from 'date-fns/parse';
import startOfWeek from 'date-fns/startOfWeek';
import getDay from 'date-fns/getDay';
import vi from 'date-fns/locale/vi';
import 'react-big-calendar/lib/css/react-big-calendar.css';

const locales = { vi };

const localizer = dateFnsLocalizer({
  format,
  parse,
  startOfWeek,
  getDay,
  locales,
});

export default function LichTrongCalendar({ lichTrong, onAddSlot, onDeleteSlot }) {
  const [deleteModal, setDeleteModal] = useState({ open: false, eventId: null });

  const events = useMemo(() => {
    return lichTrong.map((slot) => ({
      id: slot.id,
      title: `Trống - ${Number(slot.gia).toLocaleString('vi-VN')}đ`,
      start: new Date(`${slot.ngay}T${slot.gio_bat_dau}`),
      end: new Date(`${slot.ngay}T${slot.gio_ket_thuc}`),
      resource: slot,
    }));
  }, [lichTrong]);

  const handleSelectSlot = ({ start, end }) => {
    const now = new Date();
    if (start <= now) {
      alert('Không thể chọn thời gian đã qua!');
      return;
    }

    // Tùy chọn: ép buộc độ dài tối thiểu (ví dụ 60 phút)
    const minDurationMinutes = 60; // ← thay đổi số này nếu muốn (30, 60, 90, 120...)
    const durationMinutes = (end - start) / (1000 * 60);

    if (durationMinutes < minDurationMinutes) {
      alert(`Khung giờ phải ít nhất ${minDurationMinutes} phút!`);
      return;
    }

    const ngay = format(start, 'yyyy-MM-dd');
    const gio_bat_dau = format(start, 'HH:mm');
    const gio_ket_thuc = format(end, 'HH:mm');

    // Prompt giá (có thể thay bằng modal đẹp sau)
    const giaStr = prompt('Nhập giá cho khung giờ này (VNĐ):', '500000') || '';
    const gia = Number(giaStr);

    if (!gia || isNaN(gia) || gia <= 0) {
      alert('Giá không hợp lệ!');
      return;
    }

    onAddSlot({ ngay, gio_bat_dau, gio_ket_thuc, gia });
  };

  const confirmDelete = () => {
    if (deleteModal.eventId) {
      onDeleteSlot(deleteModal.eventId);
    }
    setDeleteModal({ open: false, eventId: null });
  };

  // Custom event với nút xóa khi hover
  const CustomEvent = ({ event }) => (
    <div className="relative group h-full p-1">
      <span>{event.title}</span>
      <button
        onClick={(e) => {
          e.stopPropagation();
          setDeleteModal({ open: true, eventId: event.id });
        }}
        className="absolute top-0 right-1 opacity-0 group-hover:opacity-100 transition-opacity text-red-600 hover:text-red-800 text-sm font-bold"
        title="Xóa lịch"
      >
        ×
      </button>
    </div>
  );

  return (
    <div className="h-[600px] bg-white rounded-xl shadow-lg p-4">
      <Calendar
        localizer={localizer}
        events={events}
        startAccessor="start"
        endAccessor="end"
        style={{ height: '100%' }}
        selectable
        onSelectSlot={handleSelectSlot}
        onSelectEvent={(event) => setDeleteModal({ open: true, eventId: event.id })}
        defaultView="week"
        views={['day', 'week']} // có thể thêm 'month' nếu cần
        step={30}               
        timeslots={2}           // 60 phút / 4 = 15 phút mỗi ô
        min={new Date(0, 0, 0, 6)}   // từ 6:00 sáng
        max={new Date(0, 0, 0, 23, 59)} // đến 23:59
        longPressThreshold={10} // Hỗ trợ tốt hơn trên mobile (long press để select)
        messages={{
          allDay: 'Cả ngày',
          previous: 'Trước',
          next: 'Sau',
          today: 'Hôm nay',
          month: 'Tháng',
          week: 'Tuần',
          day: 'Ngày',
          noEventsInRange: 'Không có lịch trống',
        }}
        eventPropGetter={() => ({
          style: {
            backgroundColor: '#dcfce7',
            border: '1px solid #86efac',
            color: '#166534',
          },
        })}
        components={{
          event: CustomEvent,
        }}
      />

      {/* Modal xác nhận xóa */}
      {deleteModal.open && (
        <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
          <div className="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm text-center">
            <h3 className="text-xl font-bold mb-4 text-gray-800">Xác nhận xóa</h3>
            <p className="mb-6 text-gray-600">Bạn có chắc muốn xóa lịch trống này không?</p>
            <div className="flex justify-center gap-4">
              <button
                className="btn btn-outline btn-secondary px-6"
                onClick={() => setDeleteModal({ open: false, eventId: null })}
              >
                Hủy
              </button>
              <button
                className="btn btn-error px-6"
                onClick={confirmDelete}
              >
                Xóa
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}