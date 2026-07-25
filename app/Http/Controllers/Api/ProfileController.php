<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\RequestPasswordChangeRequest;
use App\Http\Requests\Profile\UpdateProfileInfoRequest;
use App\Http\Requests\Profile\VerifyPasswordChangeRequest;
use App\Http\Resources\AuthenticatedUserResource;
use App\Notifications\SendVerificationCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProfileController extends Controller
{
    private const PENDING_PASSWORD_CACHE_PREFIX = 'admin.password.pending.';

    /**
     * Display the authenticated admin profile.
     */
    public function show(Request $request): JsonResponse
    {
        $admin = $request->user();
        $admin->loadMissing('roles', 'permissions');

        return response()->json([
            'success' => true,
            'data' => new AuthenticatedUserResource($admin),
            'message' => 'اطلاعات پروفایل با موفقیت دریافت شد',
        ]);
    }

    /**
     * Update the authenticated admin profile information.
     */
    public function updateInfo(UpdateProfileInfoRequest $request): JsonResponse
    {
        $admin = $request->user();
        $data = $request->validated();

        $imagePath = $admin->image;

        if ($request->hasFile('image')) {
            $uploadedImage = $request->file('image');
            $storedPath = $uploadedImage->store('profile', 'public');

            if (
                $imagePath
                && $imagePath !== 'noimage.png'
                && !Str::startsWith($imagePath, ['http://', 'https://'])
                && Storage::disk('public')->exists($imagePath)
            ) {
                Storage::disk('public')->delete($imagePath);
            }

            $imagePath = $storedPath;
        }

        $admin->forceFill([
            'name' => $data['name'],
            'image' => $imagePath,
        ])->save();

        return response()->json([
            'success' => true,
            'data' => new AuthenticatedUserResource($admin->fresh('roles', 'permissions')),
            'message' => 'اطلاعات پروفایل با موفقیت بروزرسانی شد',
        ]);
    }

    /**
     * Validate the password change request and send verification code.
     */
    public function requestPasswordChange(RequestPasswordChangeRequest $request): JsonResponse
    {
        $admin = $request->user();
        $data = $request->validated();

        if (!app()->environment('production')) {
            $admin->forceFill([
                'password' => Hash::make($data['password']),
            ])->save();

            return response()->json([
                'success' => true,
                'message' => 'رمز عبور بدون نیاز به تایید بروزرسانی شد.',
                'requires_verification' => false,
            ]);
        }

        $pendingPasswordKey = $this->getPendingPasswordCacheKey($admin->id);
        $hashedPassword = Hash::make($data['password']);

        Cache::put($pendingPasswordKey, $hashedPassword, now()->addMinutes(5));

        try {
            $admin->notify(new SendVerificationCode());
        } catch (Throwable $exception) {
            Cache::forget($pendingPasswordKey);

            return response()->json([
                'success' => false,
                'message' => 'ارسال کد تایید با مشکل مواجه شد. لطفا مجدداً تلاش کنید.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'کد تایید برای شما ارسال شد.',
            'requires_verification' => true,
        ]);
    }

    /**
     * Verify the password change using the provided code.
     */
    public function verifyPasswordChange(VerifyPasswordChangeRequest $request): JsonResponse
    {
        if (!app()->environment('production')) {
            return response()->json([
                'success' => true,
                'message' => 'تغییر رمز عبور در محیط غیر پروداکشن نیاز به تایید ندارد.',
            ]);
        }

        $admin = $request->user();
        $pendingPasswordKey = $this->getPendingPasswordCacheKey($admin->id);
        $pendingPassword = Cache::get($pendingPasswordKey);

        if (!$pendingPassword) {
            return response()->json([
                'success' => false,
                'message' => 'درخواست تغییر رمز عبور یافت نشد یا منقضی شده است.',
            ], 422);
        }

        $verifyCodeKey = 'verify.code.' . $admin->id;
        $cachedCode = Cache::get($verifyCodeKey);

        if (!$cachedCode || !Hash::check($request->validated()['code'], $cachedCode)) {
            return response()->json([
                'success' => false,
                'message' => 'کد تایید نامعتبر است یا منقضی شده است.',
            ], 422);
        }

        $admin->forceFill([
            'password' => $pendingPassword,
        ])->save();

        Cache::forget($pendingPasswordKey);
        Cache::forget($verifyCodeKey);

        return response()->json([
            'success' => true,
            'message' => 'رمز عبور با موفقیت بروزرسانی شد.',
        ]);
    }

    private function getPendingPasswordCacheKey(int $adminId): string
    {
        return self::PENDING_PASSWORD_CACHE_PREFIX . $adminId;
    }
}


