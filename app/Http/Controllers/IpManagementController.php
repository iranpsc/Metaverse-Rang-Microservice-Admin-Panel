<?php

namespace App\Http\Controllers;

use App\Models\Ip;
use Illuminate\Http\Request;

class IpManagementController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate(['ip' => 'required|ipv4']);

        $ip = ip2long($request->ip);

        $status = Ip::where('type', 'api')
            ->where('from', $ip)
            ->orWhere('to', $ip)
            ->exists();

        if (!$status) {
            $status = Ip::where('type', 'range')
                ->where('from', $ip)
                ->orWhere('to', $ip)
                ->exists();
        }

        if (!$status) {
            $status = Ip::where('type', 'range')
                ->where('from', '<', $ip)
                ->where('to', '>', $ip)
                ->exists();
        }
        return response()->noContent($status ? 200 : 401);
    }
}
