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
  const [priceModal, setPriceModal] = useState({ open: false, start: null, end: null });
  const [newPrice, setNewPrice] = useState('');
  const [toast, setToast] = useState(null);

  // Hàm hiển thị toast
  const showToast = (type, message) => {
    setToast({ type, message });
    setTimeout(() => setToast(null), 3500);
  };

  const events = useMemo(() => {
    return lichTrong.map((slot) => ({
      id: slot.id,
      title: `Trống - ${Number(slot.gia).toLocaleString('vi-VN')}đ`,
      start: new Date(`${slot.ngay}T${slot.gio_bat_dau}`),
      end: new Date(`${slot.ngay}T${slot.gio_ket_thuc}`),
      resource: slot,
    }));
  }, [lichTrong]);

  // Kiểm tra slot mới có trùng với slot hiện có không
  const isOverlapping = (newStart, newEnd) => {
    return events.some((event) => {
      return (
        (newStart < event.end && newEnd > event.start) // có giao nhau
      );
    });
  };

  const handleSelectSlot = ({ start, end }) => {
    const now = new Date();

    // 1. Không cho chọn thời gian đã qua
    if (start <= now) {
      showToast('warning', 'Không thể chọn thời gian đã qua hoặc đang diễn ra!');
      return;
    }

    // 2. Kiểm tra độ dài tối thiểu
    const minDurationMinutes = 60;
    const durationMinutes = (end - start) / (1000 * 60);

    if (durationMinutes < minDurationMinutes) {
      showToast('warning', `Khung giờ phải ít nhất ${minDurationMinutes} phút!`);
      return;
    }

    // 3. Kiểm tra trùng lịch
    if (isOverlapping(start, end)) {
      showToast('error', 'Khung giờ này đã được đăng ký rồi! Vui lòng chọn khung khác.');
      return;
    }

    // Mở modal nhập giá
    setPriceModal({ open: true, start, end });
    setNewPrice('500000'); // giá mặc định
  };

  const handleConfirmPrice = () => {
    const gia = Number(newPrice);

    if (!gia || isNaN(gia) || gia <= 0) {
      showToast('error', 'Vui lòng nhập giá hợp lệ (số dương)!');
      return;
    }

    const ngay = format(priceModal.start, 'yyyy-MM-dd');
    const gio_bat_dau = format(priceModal.start, 'HH:mm');
    const gio_ket_thuc = format(priceModal.end, 'HH:mm');

    onAddSlot({ ngay, gio_bat_dau, gio_ket_thuc, gia });
    showToast('success', 'Đã thêm khung giờ trống thành công!');

    // Reset modal
    setPriceModal({ open: false, start: null, end: null });
    setNewPrice('');
  };

  const confirmDelete = () => {
    if (deleteModal.eventId) {
      onDeleteSlot(deleteModal.eventId);
      showToast('success', 'Đã xóa khung giờ trống!');
    }
    setDeleteModal({ open: false, eventId: null });
  };

  const CustomEvent = ({ event }) => (
    <div className="relative group h-full p-1 overflow-hidden">
      <span className="font-medium">{event.title}</span>
      <button
        onClick={(e) => {
          e.stopPropagation();
          setDeleteModal({ open: true, eventId: event.id });
        }}
        className="absolute top-0.5 right-1.5 opacity-0 group-hover:opacity-100 transition-opacity text-red-600 hover:text-red-800 text-lg font-bold"
        title="Xóa lịch"
      >
        ×
      </button>
    </div>
  );

  return (
    <div className="h-[600px] bg-white rounded-xl shadow-lg p-4 relative">
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
        views={['day', 'week']}
        step={30}
        timeslots={2} // 60 phút chia 2 = 30 phút mỗi slot
        min={new Date(0, 0, 0, 6)}
        max={new Date(0, 0, 0, 23, 59)}
        longPressThreshold={10}
        messages={{
          allDay: 'Cả ngày',
          previous: 'Trước',
          next: 'Sau',
          today: 'Hôm nay',
          month: 'Tháng',
          week: 'Tuần',
          day: 'Ngày',
          noEventsInRange: 'Không có lịch trống trong khoảng này',
        }}
        eventPropGetter={() => ({
          style: {
            backgroundColor: '#ecfdf5',
            border: '1px solid #10b981',
            color: '#065f46',
            borderRadius: '6px',
            opacity: 0.9,
          },
        })}
        components={{
          event: CustomEvent,
        }}
      />

      {/* Toast thông báo */}
      {toast && (
        <div
          className={`fixed bottom-6 right-6 z-50 flex items-center gap-3 px-6 py-3 rounded-xl shadow-2xl text-white transition-all duration-300
            ${toast.type === 'success' ? 'bg-green-600' : 
              toast.type === 'error' ? 'bg-red-600' : 'bg-yellow-600'}`}
        >
          <span className="font-medium">{toast.message}</span>
        </div>
      )}

      {/* Modal nhập giá */}
      {priceModal.open && (
        <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
          <div className="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
            <h3 className="text-2xl font-bold mb-4 text-gray-800">Thêm khung giờ trống</h3>
            <p className="mb-4 text-gray-600">
              Thời gian: <strong>{format(priceModal.start, 'dd/MM/yyyy HH:mm')} - {format(priceModal.end, 'HH:mm')}</strong>
            </p>

            <div className="form-control mb-6">
              <label className="label">
                <span className="label-text font-medium">Giá thuê (VNĐ)</span>
              </label>
              <input
                type="number"
                className="input input-bordered input-lg w-full"
                value={newPrice}
                onChange={(e) => setNewPrice(e.target.value)}
                placeholder="Ví dụ: 500000"
                min="10000"
                autoFocus
              />
            </div>

            <div className="flex justify-end gap-4">
              <button
                className="btn btn-outline btn-secondary px-8"
                onClick={() => setPriceModal({ open: false, start: null, end: null })}
              >
                Hủy
              </button>
              <button className="btn btn-primary px-8" onClick={handleConfirmPrice}>
                Xác nhận
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Modal xác nhận xóa */}
      {deleteModal.open && (
        <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
          <div className="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm mx-4 text-center">
            <h3 className="text-xl font-bold mb-4 text-gray-800">Xác nhận xóa</h3>
            <p className="mb-6 text-gray-600">Bạn có chắc muốn xóa khung giờ trống này?</p>
            <div className="flex justify-center gap-4">
              <button
                className="btn btn-outline btn-secondary px-8"
                onClick={() => setDeleteModal({ open: false, eventId: null })}
              >
                Hủy
              </button>
              <button className="btn btn-error px-8" onClick={confirmDelete}>
                Xóa
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}