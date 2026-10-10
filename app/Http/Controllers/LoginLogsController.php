<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LoginLogsController extends Controller
{
    // Server name mapping
    protected $serverMapping = [
        'mechanomania:25565' => 'MC8 重度機械症 鐵路世界',
        // Add more servers here, e.g.
        // 'other_server:25565' => 'Other Server',
    ];

    public function index(Request $request)
    {
        if (!Schema::hasTable('login_logs')) {
            abort(404, 'Login logs table not found.');
        }

        $user = auth()->user();
        $playerNames = $user->players->pluck('name')->toArray();

        // If user has no players, return empty list early
        if (empty($playerNames)) {
            return view('user.login-logs', [
                'logs' => collect(),
                'serverMapping' => $this->serverMapping,
                'currentServer' => 'mechanomania:25565'
            ]);
        }

        $currentServer = $request->input('server', 'mechanomania:25565');

        $query = DB::table('login_logs')
            ->whereIn('username', $playerNames)
            ->orderBy('login_time', 'desc');

        if ($currentServer !== 'all') {
            $query->where('server_name', $currentServer);
        }

        $logs = $query->paginate(15);

        // Process data for display
        $logs->getCollection()->transform(function ($log) {
            $loginTimeMs = $log->login_time;
            $logoutTimeMs = $log->logout_time;

            // Convert to seconds if it seems to be in milliseconds
            if ($loginTimeMs > 2000000000) {
                $loginTimeSec = intval($loginTimeMs / 1000);
            } else {
                $loginTimeSec = $loginTimeMs;
            }
            $log->login_time_formatted = date('Y-m-d H:i:s', $loginTimeSec);

            if ($logoutTimeMs && $logoutTimeMs > 0) {
                if ($logoutTimeMs > 2000000000) {
                    $logoutTimeSec = intval($logoutTimeMs / 1000);
                } else {
                    $logoutTimeSec = $logoutTimeMs;
                }
                $durationSec = $logoutTimeSec - $loginTimeSec;
                if ($durationSec < 0) $durationSec = 0;

                $hours = floor($durationSec / 3600);
                $minutes = floor(($durationSec % 3600) / 60);
                $seconds = $durationSec % 60;

                $parts = [];
                if ($hours > 0) $parts[] = "{$hours}小時";
                if ($minutes > 0 || $hours > 0) $parts[] = "{$minutes}分";
                $parts[] = "{$seconds}秒";

                $log->play_time = implode(' ', $parts);
            } else {
                $log->play_time = '未登出 / 線上';
            }

            // Method mapping (guess standard NeoAuth methods)
            if (strtoupper($log->login_method) === 'PREMIUM') {
                $log->login_method_display = '正版驗證';
            } else if (strtoupper($log->login_method) === 'PASSWORD') {
                $log->login_method_display = '密碼輸入';
            } else if (strtoupper($log->login_method) === 'SESSION') {
                $log->login_method_display = '自動登入';
            } else {
                $log->login_method_display = $log->login_method;
            }

            // Connection channel parsing
            $log->conn_channel_display = $log->connection_channel;

            return $log;
        });

        return view('user.login-logs', [
            'logs' => $logs,
            'serverMapping' => $this->serverMapping,
            'currentServer' => $currentServer
        ]);
    }
}
