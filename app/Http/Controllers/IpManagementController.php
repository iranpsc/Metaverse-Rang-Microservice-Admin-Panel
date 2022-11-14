<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use function App\Helpers\checkIp;

class IpManagementController extends Controller
{
    public function checkIp(Request $request)
    {
        $ip = $request->input('ip');
        $ip = explode('.', $ip);

        if (file_exists(storage_path('/ip-management/ips.json'))) {
            $ips = file_get_contents(storage_path('/ip-management/ips.json'));
            $ips = json_decode($ips, true);

            if (array_key_exists('api-allowed-ips', $ips)) {
                $api_allowed_ips = $ips['api-allowed-ips'];
                foreach ($api_allowed_ips as $api_allowed_ip) {
                    $api_allowed_ip = explode('.', $api_allowed_ip['ip']);
                    if (!array_diff($ip, $api_allowed_ip)) {
                        return response()->json([
                            'ip' => implode('.', $ip),
                            'code' => 200,
                            'status' => 'allowed'
                        ]);
                    }
                }
            }

            if (array_key_exists('ip_ranges', $ips)) {
                $ip_ranges = $ips['ip_ranges'];
                foreach ($ip_ranges as $ip_range) {
                    if (!checkIp($ip_range['starting_ip'], $ip_range['ending_ip'], $ip)) {
                        return response()->json([
                            'ip' => implode('.', $ip),
                            'code' => 403,
                            'status' => 'Not allowed'
                        ]);
                    }
                }
                return response()->json([
                    'ip' => $ip,
                    'code' => 200,
                    'status' => 'Allowed'
                ]);
            }
        } else {
            return response()->json([
                'ip' => $ip,
                'code' => 403,
                'status' => 'Not allowed'
            ]);
        }
    }
}
