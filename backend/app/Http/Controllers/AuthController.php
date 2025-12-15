<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:100',
        'email' => 'required|email|unique:nguoi_dung,email',
        'password' => 'required|min:6',
        'phone' => 'nullable|string|max:20',
        'role' => 'required|in:customer,owner,admin',
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'phone' => $request->phone,
        'role' => $request->role,
    ]);

    // Nếu là chủ sân thì gửi mail xác nhận
    if ($user->role === 'owner') {
        Mail::to($user->email)->send(new \App\Mail\OwnerConfirmMail($user)); 

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công! Vui lòng kiểm tra email để xác nhận làm chủ sân.'
        ], 201);
    }

    // Nếu là khách hàng thì trả về token đăng nhập luôn
    $token = $user->createToken('api')->plainTextToken;

    return response()->json([
        'message' => 'Đăng ký thành công',
        'user' => $user,
        'token' => $token
    ], 201);
}
public function confirmOwner(Request $request)
{
    $user = User::where('email', $request->email)->firstOrFail();
    $user->email_verified_at = now();
    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'Xác nhận chủ sân thành công!'
    ]);
}

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Sai thông tin đăng nhập'], 401);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'user' => $user,
            'token' => $token
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Đã đăng xuất']);
    }
    public function updateProfile(Request $request)
{
    $user = $request->user();
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:nguoi_dung,email,' . $user->id,
        'phone' => 'nullable|string|max:15',
        'password' => 'nullable|string|min:6',
    ]);

    if (!empty($validated['password'])) {
        $validated['password'] = bcrypt($validated['password']);
    } else {
        unset($validated['password']);
    }

    $user->update($validated);

    return response()->json(['message' => 'Cập nhật thành công', 'user' => $user]);
}

}