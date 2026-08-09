<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\RequirePhoneVerification;
use App\Services\PhoneVerificationSessionService;
use Illuminate\Http\Request;
use Mockery;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class RequirePhoneVerificationMiddlewareTest extends TestCase
{
    public function test_allows_request_when_verification_disabled(): void
    {
        $session = Mockery::mock(PhoneVerificationSessionService::class);
        $session->shouldReceive('isEnabled')->once()->andReturn(false);

        $middleware = new RequirePhoneVerification($session);
        $request = Request::create('/api/dashboard', 'POST');

        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['ok' => true], $response->getData(true));
    }

    public function test_allows_get_requests_even_when_enabled(): void
    {
        $session = Mockery::mock(PhoneVerificationSessionService::class);
        $session->shouldReceive('isEnabled')->once()->andReturn(true);

        $middleware = new RequirePhoneVerification($session);
        $request = Request::create('/api/dashboard', 'GET');

        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_allows_excluded_mutating_paths_when_enabled(): void
    {
        $session = Mockery::mock(PhoneVerificationSessionService::class);
        $session->shouldReceive('isEnabled')->once()->andReturn(true);

        $middleware = new RequirePhoneVerification($session);
        $request = Request::create('/api/login', 'POST');

        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_allows_verified_mutating_requests_when_enabled(): void
    {
        $session = Mockery::mock(PhoneVerificationSessionService::class);
        $session->shouldReceive('isEnabled')->once()->andReturn(true);
        $session->shouldReceive('isVerified')->once()->andReturn(true);

        $middleware = new RequirePhoneVerification($session);
        $request = Request::create('/api/dashboard', 'PUT');

        $response = $middleware->handle($request, fn () => response()->json(['ok' => true]));

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_blocks_unverified_mutating_requests_with_423(): void
    {
        $session = Mockery::mock(PhoneVerificationSessionService::class);
        $session->shouldReceive('isEnabled')->once()->andReturn(true);
        $session->shouldReceive('isVerified')->once()->andReturn(false);

        $middleware = new RequirePhoneVerification($session);
        $request = Request::create('/api/dashboard', 'DELETE');

        $response = $middleware->handle($request, function () {
            $this->fail('Next middleware should not run when verification is required.');
        });

        $this->assertSame(423, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'تایید شماره موبایل منقضی شده است. لطفاً مجدداً تایید کنید.',
            'requires_phone_verification' => true,
        ], $response->getData(true));
        $this->assertInstanceOf(Response::class, $response);
    }
}
