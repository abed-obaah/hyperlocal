<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:30',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'role' => 'customer',
            'avatar' => 'https://i.pravatar.cc/200?u='.urlencode($data['email']),
            // Every new customer gets a empty wallet by default.
            'wallet_balance' => 0.00,
        ]);

        return response()->json([
            'user' => $this->payload($user),
            'token' => $user->createToken('mobile')->plainTextToken,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }

        return response()->json([
            'user' => $this->payload($user),
            'token' => $user->createToken('mobile')->plainTextToken,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $this->payload($request->user())]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    /**
     * Issue a 6-digit reset code. The code is hashed and stored in the standard
     * password_reset_tokens table with a fresh timestamp. With MAIL_MAILER=log
     * there is no real inbox, so the code is also written to the log for testing.
     * Always returns a generic success so the endpoint can't be used to probe
     * which emails are registered.
     */
    public function forgotPassword(Request $request)
    {
        $data = $request->validate(['email' => 'required|email']);

        $user = User::where('email', $data['email'])->first();
        if ($user) {
            $code = (string) random_int(100000, 999999);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                ['token' => Hash::make($code), 'created_at' => now()],
            );

            // No real mailer in this environment — surface the code in the log.
            Log::info("Password reset code for {$user->email}: {$code}");
        }

        return response()->json([
            'message' => 'If that email is registered, a reset code has been sent.',
        ]);
    }

    /**
     * Verify the 6-digit code and set a new password. Codes expire after 15
     * minutes. On success the old reset record and all existing tokens are
     * cleared and the user is signed straight back in.
     */
    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $data['email'])->first();
        $invalid = ValidationException::withMessages(['code' => ['Invalid or expired code.']]);

        if (! $record || ! Hash::check($data['code'], $record->token)) {
            throw $invalid;
        }
        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            throw $invalid;
        }

        $user = User::where('email', $data['email'])->firstOrFail();
        $user->update(['password' => $data['password']]);

        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
        $user->tokens()->delete();

        return response()->json([
            'user' => $this->payload($user),
            'token' => $user->createToken('mobile')->plainTextToken,
        ]);
    }

    private function payload(User $user): array
    {
        return [
            'id' => (string) $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'role' => $user->role,
            'isAvailable' => (bool) $user->is_available,
            'walletBalance' => (float) $user->wallet_balance,
        ];
    }
}
