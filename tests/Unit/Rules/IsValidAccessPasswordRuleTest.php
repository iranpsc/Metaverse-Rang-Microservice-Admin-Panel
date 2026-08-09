<?php

namespace Tests\Unit\Rules;

use App\Models\Admin;
use App\Rules\IsValidAccessPassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\CreatesAuthApiSchema;
use Tests\TestCase;

class IsValidAccessPasswordRuleTest extends TestCase
{
    use CreatesAuthApiSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpAuthApiSchema();
    }

    public function test_passes_when_access_password_matches(): void
    {
        $admin = Admin::query()->create([
            'name' => 'Access Admin',
            'email' => 'access-admin@example.com',
            'password' => Hash::make('password'),
            'active' => 1,
        ]);
        $admin->access_password = Hash::make('Access#123');

        Auth::guard('admin')->setUser($admin);

        $rule = new IsValidAccessPassword;

        $this->assertTrue($rule->passes('access_password', 'Access#123'));
        $this->assertFalse($rule->passes('access_password', 'wrong'));
        $this->assertSame('رمز دسترسی صحیح نیست!', $rule->message());
    }
}
