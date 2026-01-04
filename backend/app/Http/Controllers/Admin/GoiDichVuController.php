<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GoiDichVu;

class GoiDichVuController extends Controller
{
    private function validateData(Request $request)
{
    return $request->validate([
        'ten_goi' => 'required|string|max:100',
        'mo_ta' => 'nullable|string',
        'gia' => 'required|numeric|min:0',
        'thoi_han' => 'required|integer|min:1',
        'trang_thai' => 'required|in:hoat_dong,ngung_ban'
    ]);
}

    //Lấy danh sách tất cả gói dịch vụ
    public function index(Request $request)
{
    $query = GoiDichVu::query();

    if ($request->filled('trang_thai')) {
        $query->where('trang_thai', $request->trang_thai);
    }

    return response()->json(
        $query->orderByDesc('id')->paginate(10)
    );
}

    //Thêm gói mới
    public function store(Request $request)
{
    $goi = GoiDichVu::create($this->validateData($request));

    return response()->json([
        'message' => 'Thêm gói thành công',
        'data' => $goi
    ], 201);
}


    //Sửa gói dịch vụ
    public function update(Request $request, $id)
{
    $goi = GoiDichVu::findOrFail($id);
    $goi->update($this->validateData($request));

    return response()->json([
        'message' => 'Cập nhật thành công',
        'data' => $goi
    ]);
}


    //Xóa gói dịch vụ
    public function destroy($id)
{
    GoiDichVu::findOrFail($id)->delete();

    return response()->json([
        'message' => 'Đã xóa gói thành công'
    ]);
}

}
