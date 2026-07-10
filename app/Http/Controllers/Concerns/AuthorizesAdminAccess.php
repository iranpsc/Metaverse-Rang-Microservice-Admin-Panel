<?php

namespace App\Http\Controllers\Concerns;

trait AuthorizesAdminAccess
{
  /**
   * Register Spatie role/permission middleware with super-admin bypass.
   *
   * @param  string|array<int, string>  $rolesOrPermissions
   */
  protected function authorizeAdminAccess(string|array $rolesOrPermissions): void
  {
    $items = is_array($rolesOrPermissions) ? $rolesOrPermissions : [$rolesOrPermissions];
    array_unshift($items, 'super-admin');

    $this->middleware('role_or_permission:' . implode('|', $items));
  }
}
