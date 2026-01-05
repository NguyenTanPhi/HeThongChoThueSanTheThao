<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
        'current_password'      => 'nullable|string',
        'password'              => 'nullable|string|min:6|confirmed',
    ]);

    // Nếu gửi password mới → phải có current_password và đúng
    if (!empty($validated['password'])) {
        if (empty($validated['current_password'])) {
            return response()->json(['message' => 'Vui lòng nhập mật khẩu hiện tại để đổi mật khẩu mới'], 422);
        }

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'Mật khẩu hiện tại không đúng'], 422);
        }

        $validated['password'] = Hash::make($validated['password']);
    } else {
        unset($validated['password']);
        unset($validated['current_password']);
    }

    $user->update($validated);

    return response()->json([
        'message' => 'Cập nhật thông tin thành công',
        'user'    => $user->fresh()
    ]);
}

public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email'
    ]);

    $user = User::where('email', $request->email)->first();

    // Không tiết lộ email tồn tại hay không
    if (!$user) {
        return response()->json([
            'message' => 'Nếu email tồn tại, link đặt lại mật khẩu đã được gửi'
        ]);
    }

    $token = Str::random(64);

    DB::table('password_resets')->updateOrInsert(
        ['email' => $request->email],
        [
            'token' => Hash::make($token),
            'created_at' => Carbon::now()
        ]
    );

  $resetLink = env('URL_FRONTEND_RESET_PASSWORD') . "/reset-password?token=" . $token;

   Mail::send([], [], function ($message) use ($request, $resetLink) {
    $message->to($request->email)
        ->subject('Đặt lại mật khẩu')
        ->setBody(
            'Bấm vào <a href="'.$resetLink.'">đây</a> để đặt lại mật khẩu. Link có hiệu lực 15 phút.',
            'text/html'
        );
        }
    );

    return response()->json([
        'message' => 'Nếu email tồn tại, link đặt lại mật khẩu đã được gửi'
    ]);
}

public function resetPassword(Request $request)
{
    $request->validate([
        'token' => 'required',
        'password' => 'required|min:6|confirmed'
    ]);

    $reset = DB::table('password_resets')->first();

    if (!$reset || !Hash::check($request->token, $reset->token)) {
        return response()->json(['message' => 'Token không hợp lệ'], 400);
    }

    // Hết hạn sau 15 phút
    if (Carbon::parse($reset->created_at)->addMinutes(15)->isPast()) {
        return response()->json(['message' => 'Token đã hết hạn'], 400);
    }

    $user = User::where('email', $reset->email)->first();

    if (!$user) {
        return response()->json(['message' => 'User không tồn tại'], 404);
    }

    $user->password = Hash::make($request->password);
    $user->save();

    DB::table('password_resets')->where('email', $reset->email)->delete();

    return response()->json([
        'message' => 'Đổi mật khẩu thành công'
    ]);
}

}