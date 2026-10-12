<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\MessageResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return (new AuthResource($result))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Authenticate user and return a Sanctum access token.
     */
    public function login(LoginRequest $request): AuthResource
    {
        $result = $this->authService->login(
            $request->getLoginIdentifier(),
            $request->string('password')
        );

        return new AuthResource($result);
    }

    /**
     * Retrieve the currently authenticated user.
     */
    public function me(Request $request): UserResource
    {
        $user = $request->user()->load('profile');

        return new UserResource($user);
    }

    /**
     * Revoke current access token (logout).
     */
    public function logout(Request $request): MessageResource
    {
        $this->authService->logout($request->user());

        return new MessageResource('Logged out successfully');
    }

    /**
     * Send password reset link to user email.
     */
    public function forgotPassword(ForgotPasswordRequest $request): MessageResource
    {
        $this->authService->sendPasswordResetLink($request->string('email'));

        return new MessageResource('Password reset link sent to your email.');
    }

    /**
     * Reset user password using token.
     */
    public function resetPassword(ResetPasswordRequest $request): MessageResource
    {
        $this->authService->resetPassword($request->validated());

        return new MessageResource('Password reset successfully.');
    }

    /**
     * Verify user email address via signed URL.
     */
    public function verifyEmail(Request $request, int|string $id, string $hash): MessageResource
    {
        $user = User::findOrFail($id);

        $status = $this->authService->verifyEmail($user, $hash);

        $message = $status ? 'Email verified successfully.' : 'Email already verified.';

        return new MessageResource($message);
    }

    /**
     * Resend email verification notification.
     */
    public function resendVerificationEmail(Request $request): MessageResource
    {
        $sent = $this->authService->sendEmailVerification($request->user());

        $message = $sent ? 'Verification link sent.' : 'Email already verified.';

        return new MessageResource($message);
    }
}
