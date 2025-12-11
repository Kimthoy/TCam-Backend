<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // ===========================
    // REGISTER
    // ===========================
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:191',
            'email'    => 'required|email|max:191|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'photo'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => $request->input('role', 'customer'),
            'is_active' => true,
        ]);

        // Photo Upload
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store("users/{$user->id}", 'public');
            $user->photo = $path;
            $user->save();
        }

        // Create JWT Token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Registered successfully',
            'token'   => $token,
            'user'    => $user,
        ], 201);
    }

    // ===========================
    // LOGIN
    // ===========================
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Try to login using JWT
        if (! $token = auth('api')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials.'
            ]);
        }

        $user = auth('api')->user();

        if (! $user->is_active) {
            return response()->json(['message' => 'Account is deactivated'], 403);
        }

        return response()->json([
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => $user
        ]);
    }

    // ===========================
    // LOGOUT (Invalidate Token)
    // ===========================
    public function logout()
    {
        auth('api')->logout(); // invalidate JWT

        return response()->json(['message' => 'Logged out']);
    }

    // ===========================
    // GET CURRENT USER
    // ===========================
    public function me()
    {
        return response()->json(auth('api')->user());
    }

    // ===========================
    // UPDATE PROFILE
    // ===========================
    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();

        $data = $request->validate([
            'name'  => 'sometimes|required|string|max:191',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:191',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if (isset($data['name']))  $user->name  = $data['name'];
        if (isset($data['email'])) $user->email = $data['email'];

        // Photo update
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store("users/{$user->id}", 'public');

            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }

            $user->photo = $path;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'data'    => $user
        ]);
    }

    // ===========================
    // CHANGE PASSWORD
    // ===========================
    public function changePassword(Request $request)
    {
        $user = auth('api')->user();

        $data = $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        return response()->json(['success' => true, 'message' => 'Password changed']);
    }

    // ===========================
    // FORGOT PASSWORD
    // ===========================
    public function forgotPassword(Request $request)
    {
        $data = $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(['email' => $data['email']]);

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['success' => true, 'message' => __($status)])
            : response()->json(['success' => false, 'message' => __($status)], 422);
    }

    // ===========================
    // RESET PASSWORD
    // ===========================
    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset($data, function ($user, $password) {
            $user->password = Hash::make($password);
            $user->setRememberToken(Str::random(60));
            $user->save();

            event(new PasswordReset($user));
        });

        return $status === Password::PASSWORD_RESET
            ? response()->json(['success' => true, 'message' => __($status)])
            : response()->json(['success' => false, 'message' => __($status)], 422);
    }
}
