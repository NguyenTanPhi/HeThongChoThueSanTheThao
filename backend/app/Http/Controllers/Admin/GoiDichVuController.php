<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GoiDichVu;

class GoiDichVuController extends Controller
{
    //Lấy danh sách tất cả gói dịch vụ
    public function index()
    {
        return response()->json(GoiDichVu::orderBy('id', 'desc')->get());
    }
    //Thêm gói mới
    public function store(Request $request)
    {
        $data = $request->validate([
            'ten_goi' => 'required|string|max:100',
            'mo_ta' => 'nullable|string',
            'gia' => 'required|numeric|min:0',
            'thoi_han' => 'required|integer|min:1',
            'trang_thai' => 'required|in:hoat_dong,ngung_ban'
        ]);

        $goi = GoiDichVu::create($data);
        return response()->json([
            'message' => 'Thêm gói thành công',
            'data' => $goi
        ]);
    }

    //Sửa gói dịch vụ
    public function update(Request $request, $id)
    {
        $goi = GoiDichVu::findOrFail($id);

        $data = $request->validate([
            'ten_goi' => 'required|string|max:100',
            'mo_ta' => 'nullable|string',
            'gia' => 'required|numeric|min:0',
            'thoi_han' => 'required|integer|min:1',
            'trang_thai' => 'required|in:hoat_dong,ngung_ban'
        ]);

        $goi->update($data);

        return response()->json([
            'message' => 'Cập nhật thành công',
            'data' => $goi
        ]);
    }

    //Xóa gói dịch vụ
    public function destroy($id)
    {
        $goi = GoiDichVu::find($id);
        if (!$goi) {
            return response()->json(['message' => 'Không tìm thấy gói'], 404);
        }

        $goi->delete();
        return response()->json(['message' => 'Đã xóa gói thành công']);
    }
}
