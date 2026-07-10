<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketDepartmentPermissions
{
  public const MAP = [
    'citizens_safety' => 'respond-to-citziens-safety-tickets',
    'technical_support' => 'respond-to-technical-support-tickets',
    'investment' => 'respond-to-investment-tickets',
    'inspection' => 'respond-to-inspection-tickets',
    'protection' => 'respond-to-protection-tickets',
    'ztb' => 'respond-to-ztb-management-tickets',
  ];

  public static function all(): array
  {
    return array_values(self::MAP);
  }

  public static function forDepartment(?string $department): ?string
  {
    if ($department === null || $department === '') {
      return null;
    }

    return self::MAP[$department] ?? null;
  }

  public static function denyUnlessAuthorized(Request $request, ?string $department = null): ?JsonResponse
  {
    $user = $request->user();

    if (! $user) {
      return response()->json([
        'success' => false,
        'message' => 'کاربر احراز هویت نشده است.',
      ], 401);
    }

    if ($user->hasRole('super-admin') || $user->hasRole('support-management')) {
      return null;
    }

    $department = $department ?? $request->query('department') ?? $request->input('department');
    $permission = self::forDepartment(is_array($department) ? ($department[0] ?? null) : $department);

    if ($permission === null || ! $user->can($permission)) {
      return response()->json([
        'success' => false,
        'message' => 'شما دسترسی لازم برای این بخش پشتیبانی را ندارید.',
      ], 403);
    }

    return null;
  }
}
