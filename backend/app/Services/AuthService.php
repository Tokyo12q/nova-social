<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user and initialize a blank profile within a database transaction.
     *
     * @param  array<string, mixed>  $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'is_active' => true,
                'role' => 'user',
            ]);

            $user->profile()->create([]);

            event(new Registered($user));

            $token = $user->createToken('auth_token')->plainTextToken;
            $user->load('profile');

            return [
                'user' => $user,
                'token' => $token,
            ];
        });
    }

    /**
     * Authenticate a user via login (username or email) and password.
     *
     * @return array{user: User, token: string}
     */
    public function login(string $login, string $password): array
    {
        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            abort(401, 'Invalid credentials.');
        }

        if (! $user->is_active) {
            abort(403, 'Your account is deactivated.');
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->load('profile');

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Invalidate the current personal access token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();

        if (app()->bound('auth')) {
            app('auth')->forgetGuards();
        }
    }

    /**
     * Send password reset link to user email.
     *
     * @throws ValidationException
     */
    public function sendPasswordResetLink(string $email): void
    {
        $status = Password::broker()->sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }

    /**
     * Reset user password using the provided credentials and reset token.
     *
     * @param  array<string, mixed>  $credentials
     *
     * @throws ValidationException
     */
    public function resetPassword(array $credentials): void
    {
        $status = Password::broker()->reset(
            $credentials,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                // Revoke all existing tokens on password reset for security
                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }

    /**
     * Verify email address for the user.
     */
    public function verifyEmail(User $user, string $hash): bool
    {
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $user->markEmailAsVerified();

        event(new Verified($user));

        return true;
    }

    /**
     * Send email verification notification to the user.
     */
    public function sendEmailVerification(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }

        $user->sendEmailVerificationNotification();

        return true;
    }
}
