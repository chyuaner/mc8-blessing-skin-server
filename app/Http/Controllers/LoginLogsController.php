<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LoginLogsController extends Controller
{
    // Server name mapping
    protected $serverMapping = [
        'mechanomania:25565' => 'MC8 重度機械症 鐵路世界',
        'server2:25565' => 'Barian主服 落幕曲',
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

        // If user has no players, return early with empty collections
        if (empty($playerNames)) {
            return view('user.login-logs', [
                'logs' => new LengthAwarePaginator(collect(), 0, 30),
                'serverMapping' => $this->serverMapping,
                'currentServer' => 'mechanomania:25565',
                'filterOptions' => [
                    'servers' => $this->serverMapping,
                    'usernames' => [],
                    'ips' => [],
                    'uuids' => [],
                    'loginMethods' => [],
                    'connectionChannels' => [],
                    'methodLabels' => [],
                ],
                'filters' => [
                    'server' => 'mechanomania:25565',
                    'username' => 'all',
                    'ip' => 'all',
                    'uuid' => 'all',
                    'login_method' => 'all',
                    'connection_channel' => 'all',
                    'start_time' => '',
                    'end_time' => '',
                    'sort' => 'login_time_desc',
                    'per_page' => 30,
                ],
            ]);
        }

        $baseLogs = DB::table('login_logs')->whereIn('username', $playerNames);

        // Fetch distinct servers for top-level selector
        $dbServers = (clone $baseLogs)->whereNotNull('server_name')->where('server_name', '!=', '')->distinct()->pluck('server_name')->toArray();
        $serverOptions = $this->serverMapping;
        foreach ($dbServers as $s) {
            if (!isset($serverOptions[$s])) {
                $serverOptions[$s] = $s;
            }
        }

        // Parameters: Server is outermost
        $currentServer = $request->input('server', 'mechanomania:25565');

        // Scope filter dropdown options according to the selected server
        if ($currentServer !== 'all' && !empty($currentServer)) {
            $serverScopedLogs = (clone $baseLogs)->where('server_name', $currentServer);
        } else {
            $serverScopedLogs = clone $baseLogs;
        }

        $availableUsernames = $playerNames;
        $availableIps = (clone $serverScopedLogs)->whereNotNull('ip')->where('ip', '!=', '')->distinct()->pluck('ip')->toArray();
        $availableUuids = (clone $serverScopedLogs)->whereNotNull('uuid')->where('uuid', '!=', '')->distinct()->pluck('uuid')->toArray();
        $availableLoginMethods = (clone $serverScopedLogs)->whereNotNull('login_method')->where('login_method', '!=', '')->distinct()->pluck('login_method')->toArray();
        $availableChannels = (clone $serverScopedLogs)->whereNotNull('connection_channel')->where('connection_channel', '!=', '')->distinct()->pluck('connection_channel')->toArray();

        // Other Parameters
        $username = $request->input('username', 'all');
        $ip = $request->input('ip', 'all');
        $uuid = $request->input('uuid', 'all');
        $loginMethod = $request->input('login_method', 'all');
        $connChannel = $request->input('connection_channel', 'all');
        $startTime = $request->input('start_time', '');
        $endTime = $request->input('end_time', '');
        $sort = $request->input('sort', 'login_time_desc');
        $perPage = (int) $request->input('per_page', 30);
        if (!in_array($perPage, [30, 60, 120])) {
            $perPage = 30;
        }

        // Build query
        $query = DB::table('login_logs')->whereIn('username', $playerNames);

        // Filter: Server (topmost filter)
        if ($currentServer !== 'all' && !empty($currentServer)) {
            $query->where('server_name', $currentServer);
        }

        // Filter: Username
        if ($username !== 'all' && !empty($username)) {
            $query->where('username', $username);
        }

        // Filter: IP
        if ($ip !== 'all' && !empty($ip)) {
            $query->where('ip', $ip);
        }

        // Filter: UUID
        if ($uuid !== 'all' && !empty($uuid)) {
            $query->where('uuid', $uuid);
        }

        // Filter: Login Method
        if ($loginMethod !== 'all' && !empty($loginMethod)) {
            $query->where('login_method', $loginMethod);
        }

        // Filter: Connection Channel
        if ($connChannel !== 'all' && !empty($connChannel)) {
            $query->where('connection_channel', $connChannel);
        }

        // Filter: Start Time (login_time >= start_time)
        if (!empty($startTime)) {
            $startTs = strtotime($startTime);
            if ($startTs !== false) {
                $query->where('login_time', '>=', $startTs * 1000);
            }
        }

        // Filter: End Time (login_time <= end_time)
        if (!empty($endTime)) {
            $endStr = $endTime;
            if (strlen($endStr) === 10) {
                $endStr .= ' 23:59:59';
            }
            $endTs = strtotime($endStr);
            if ($endTs !== false) {
                $query->where('login_time', '<=', $endTs * 1000);
            }
        }

        // Sorting
        switch ($sort) {
            case 'login_time_asc':
                $query->orderBy('login_time', 'asc');
                break;
            case 'duration_desc':
                $query->orderByRaw('(CASE WHEN logout_time IS NOT NULL AND logout_time > login_time THEN (logout_time - login_time) ELSE 0 END) DESC')
                      ->orderBy('login_time', 'desc');
                break;
            case 'duration_asc':
                $query->orderByRaw('(CASE WHEN logout_time IS NOT NULL AND logout_time > login_time THEN (logout_time - login_time) ELSE 0 END) ASC')
                      ->orderBy('login_time', 'desc');
                break;
            case 'username_asc':
                $query->orderBy('username', 'asc')->orderBy('login_time', 'desc');
                break;
            case 'username_desc':
                $query->orderBy('username', 'desc')->orderBy('login_time', 'desc');
                break;
            case 'login_time_desc':
            default:
                $query->orderBy('login_time', 'desc');
                break;
        }

        $logs = $query->paginate($perPage);

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
                $durationSec = max(0, $logoutTimeSec - $loginTimeSec);

                $hours = floor($durationSec / 3600);
                $minutes = floor(($durationSec % 3600) / 60);
                $seconds = $durationSec % 60;

                $parts = [];
                if ($hours > 0) $parts[] = "{$hours}小時";
                if ($minutes > 0 || $hours > 0) $parts[] = "{$minutes}分";
                $parts[] = "{$seconds}秒";

                $log->play_time = implode(' ', $parts);
                $log->is_online = false;
            } else {
                $log->play_time = '未知';
                $log->is_online = true;
            }

            // Login Method mapping
            $methodUpper = strtoupper($log->login_method ?? '');
            if ($methodUpper === 'PREMIUM') {
                $log->login_method_display = '正版驗證';
                $log->login_method_badge = 'badge-success';
            } elseif ($methodUpper === 'PASSWORD') {
                $log->login_method_display = '密碼輸入';
                $log->login_method_badge = 'badge-primary';
            } elseif ($methodUpper === 'SESSION') {
                $log->login_method_display = '自動登入';
                $log->login_method_badge = 'badge-info';
            } else {
                $log->login_method_display = $log->login_method ?: '-';
                $log->login_method_badge = 'badge-secondary';
            }

            // Connection Channel formatting according to NeoAuthReloaded TabIntegration
            $rawChannel = !empty($log->connection_channel) ? trim($log->connection_channel) : 'TCP';
            $pop = !empty($log->cdn_pop) ? trim($log->cdn_pop) : '';

            if (str_contains($rawChannel, 'CDN')) {
                if (!empty($pop)) {
                    if (str_contains($rawChannel, '+zstd')) {
                        $channelText = str_replace('+zstd', '(' . $pop . ')+zstd', $rawChannel);
                    } else {
                        $channelText = $rawChannel . '(' . $pop . ')';
                    }
                } else {
                    $channelText = $rawChannel;
                }
                $badgeClass = 'badge badge-warning';
                $style = 'background-color: #f39c12; color: #fff; font-weight: 600;';
            } elseif (str_contains($rawChannel, 'WebSocket')) {
                $channelText = $rawChannel;
                $badgeClass = 'badge badge-info';
                $style = 'background-color: #00c0ef; color: #fff; font-weight: 600;';
            } elseif (str_contains($rawChannel, 'TCP')) {
                $channelText = $rawChannel;
                $badgeClass = 'badge badge-success';
                $style = 'background-color: #00a65a; color: #fff; font-weight: 600;';
            } else {
                $channelText = $rawChannel;
                $badgeClass = 'badge badge-secondary';
                $style = 'background-color: #6c757d; color: #fff; font-weight: 600;';
            }

            $log->conn_channel_display = $channelText;
            $log->conn_channel_badge_class = $badgeClass;
            $log->conn_channel_style = $style;

            return $log;
        });

        // Method label mapping for dropdown
        $methodLabels = [
            'Password' => '密碼輸入 (Password)',
            'Premium' => '正版驗證 (Premium)',
            'Session' => '自動登入 (Session)',
        ];

        return view('user.login-logs', [
            'logs' => $logs,
            'serverMapping' => $this->serverMapping,
            'currentServer' => $currentServer,
            'filterOptions' => [
                'servers' => $serverOptions,
                'usernames' => $availableUsernames,
                'ips' => $availableIps,
                'uuids' => $availableUuids,
                'loginMethods' => $availableLoginMethods,
                'connectionChannels' => $availableChannels,
                'methodLabels' => $methodLabels,
            ],
            'filters' => [
                'server' => $currentServer,
                'username' => $username,
                'ip' => $ip,
                'uuid' => $uuid,
                'login_method' => $loginMethod,
                'connection_channel' => $connChannel,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'sort' => $sort,
                'per_page' => $perPage,
            ],
        ]);
    }
}
