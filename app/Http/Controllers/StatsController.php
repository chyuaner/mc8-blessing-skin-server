<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class StatsController extends Controller
{
    /**
     * 維度中文名稱對照表
     */
    protected array $dimensionLabels = [
        'overworld' => '主世界 (Overworld)',
        'the_nether' => '地獄 (The Nether)',
        'the_end' => '終界 (The End)',
        'twilight_forest' => '暮光森林 (Twilight Forest)',
        'pocket_dimension' => '口袋維度 (Pocket Dimension)',
        'the_retreat' => '避難所 (The Retreat)',
    ];

    /**
     * 檢查 Prometheus 是否啟用或處於 mock 模式
     *
     * @return string|bool 'mock', true, 或 false
     */
    protected function getPrometheusMode(): string|bool
    {
        $enabled = config('services.prometheus.enabled');
        if ($enabled === 'mock') {
            return 'mock';
        }
        if ($enabled === true || $enabled === 'true' || $enabled === '1' || $enabled === 1) {
            return true;
        }
        return false;
    }

    /**
     * 伺服器負荷監控前端頁面
     */
    public function show()
    {
        $mode = $this->getPrometheusMode();
        if (!$mode) {
            abort(404);
        }

        return view('pages.stats')
            ->with('is_mock', $mode === 'mock')
            ->with('site_name', option('site_name', config('options.site_name', 'Minecraft 伺服器')));
    }

    /**
     * 取得即時摘要資訊 (TPS, MSPT, 玩家, 記憶體百分比, 區塊等)
     */
    public function summary()
    {
        $mode = $this->getPrometheusMode();
        if (!$mode) {
            abort(404);
        }

        if ($mode === 'mock') {
            return response()->json($this->generateMockSummary());
        }

        // 短暫快取 5 秒，避免重複連線打底層 VictoriaMetrics
        $data = Cache::remember('mc_stats_summary', 5, function () {
            return $this->fetchLiveSummary();
        });

        return response()->json($data);
    }

    /**
     * 取得歷史時間序列數據 (以 range 劃分)
     */
    public function history(Request $request)
    {
        $mode = $this->getPrometheusMode();
        if (!$mode) {
            abort(404);
        }

        $range = $request->query('range', '1h');
        $rangeMap = [
            '1h'  => ['seconds' => 3600,   'step' => '30s'],
            '6h'  => ['seconds' => 21600,  'step' => '120s'],
            '24h' => ['seconds' => 86400,  'step' => '600s'],
            '7d'  => ['seconds' => 604800, 'step' => '3600s'],
        ];

        if (!isset($rangeMap[$range])) {
            $range = '1h';
        }

        if ($mode === 'mock') {
            return response()->json($this->generateMockHistory($range, $rangeMap[$range]));
        }

        $cacheKey = "mc_stats_history_{$range}";
        $data = Cache::remember($cacheKey, 30, function () use ($range, $rangeMap) {
            return $this->fetchLiveHistory($rangeMap[$range]);
        });

        return response()->json($data);
    }

    /**
     * 查詢 VictoriaMetrics 的即時向量 (Instant Query)
     */
    protected function queryPrometheusInstant(string $query): ?array
    {
        $baseUrl = rtrim(config('services.prometheus.url', 'http://127.0.0.1:8428'), '/');
        try {
            $response = Http::timeout(3)->get("{$baseUrl}/api/v1/query", [
                'query' => $query,
            ]);

            if ($response->successful() && $response->json('status') === 'success') {
                return $response->json('data.result') ?: [];
            }
        } catch (\Throwable $e) {
            // 忽略連線失敗，由上層處理 fallback
        }
        return null;
    }

    /**
     * 抓取真實即時監控數據
     */
    protected function fetchLiveSummary(): array
    {
        $job = config('services.prometheus.job', 'mc8-mechanomania');
        $selector = "{job=\"{$job}\"}";

        $upResult = $this->queryPrometheusInstant("up{$selector}");
        $isOnline = false;
        if (!empty($upResult) && isset($upResult[0]['value'][1])) {
            $isOnline = (float) $upResult[0]['value'][1] > 0;
        }

        if (!$isOnline) {
            return [
                'status' => 'offline',
                'is_mock' => false,
                'server_online' => false,
                'last_updated' => now()->toIso8601String(),
                'metrics' => [
                    'tps' => 0.0,
                    'mspt' => 0.0,
                    'players_online' => 0,
                    'chunks_loaded' => 0,
                    'entities_total' => 0,
                    'heap_percent' => 0.0,
                    'cpu_percent' => 0.0,
                    'dimensions' => [],
                ],
            ];
        }

        // 提取單一標量輔助函式
        $extractScalar = function (?array $result, float $default = 0.0): float {
            if (!empty($result) && isset($result[0]['value'][1])) {
                return (float) $result[0]['value'][1];
            }
            return $default;
        };

        $tps = $extractScalar($this->queryPrometheusInstant("clamp_max(rate(mc_server_tick_seconds_count{$selector}[1m]), 20)"), 20.0);
        $mspt = $extractScalar($this->queryPrometheusInstant("rate(mc_server_tick_seconds_sum{$selector}[1m]) / rate(mc_server_tick_seconds_count{$selector}[1m]) * 1000"), 0.0);
        $players = (int) $extractScalar($this->queryPrometheusInstant("sum(mc_entities_total{job=\"{$job}\", type=\"Player\"}) or vector(0)"), 0);
        $chunks = (int) $extractScalar($this->queryPrometheusInstant("sum(mc_dimension_chunks_loaded{$selector}) or vector(0)"), 0);
        $entities = (int) $extractScalar($this->queryPrometheusInstant("sum(mc_entities_total{$selector}) or vector(0)"), 0);
        // 安全起見，僅計算百分比，不外洩實體記憶體配置容量大小
        $heapPercent = $extractScalar($this->queryPrometheusInstant("jvm_memory_bytes_used{job=\"{$job}\", area=\"heap\"} / jvm_memory_bytes_max{job=\"{$job}\", area=\"heap\"} * 100"), 0.0);
        $cpu = $extractScalar($this->queryPrometheusInstant("rate(process_cpu_seconds_total{$selector}[1m]) * 100"), 0.0);

        // 各維度細項
        $dimMsptResults = $this->queryPrometheusInstant("rate(mc_dimension_tick_seconds_sum{$selector}[1m]) / rate(mc_dimension_tick_seconds_count{$selector}[1m]) * 1000") ?: [];
        $dimChunksResults = $this->queryPrometheusInstant("mc_dimension_chunks_loaded{$selector}") ?: [];

        $chunksMap = [];
        foreach ($dimChunksResults as $item) {
            $name = $item['metric']['name'] ?? '';
            if ($name !== '') {
                $chunksMap[$name] = (int) ($item['value'][1] ?? 0);
            }
        }

        $dimensions = [];
        foreach ($dimMsptResults as $item) {
            $name = $item['metric']['name'] ?? 'unknown';
            $val = (float) ($item['value'][1] ?? 0);
            $dimensions[] = [
                'name' => $name,
                'label' => $this->dimensionLabels[$name] ?? ucfirst(str_replace('_', ' ', $name)),
                'mspt' => round($val, 2),
                'chunks' => $chunksMap[$name] ?? 0,
            ];
        }

        return [
            'status' => 'success',
            'is_mock' => false,
            'server_online' => true,
            'last_updated' => now()->toIso8601String(),
            'metrics' => [
                'tps' => round($tps, 2),
                'mspt' => round($mspt, 2),
                'players_online' => $players,
                'chunks_loaded' => $chunks,
                'entities_total' => $entities,
                'heap_percent' => round($heapPercent, 1),
                'cpu_percent' => round($cpu, 1),
                'dimensions' => $dimensions,
            ],
        ];
    }

    /**
     * 抓取真實歷史走勢數據 (Range Query)
     */
    protected function fetchLiveHistory(array $config): array
    {
        $baseUrl = rtrim(config('services.prometheus.url', 'http://127.0.0.1:8428'), '/');
        $job = config('services.prometheus.job', 'mc8-mechanomania');
        $selector = "{job=\"{$job}\"}";

        $end = time();
        $start = $end - $config['seconds'];
        $step = $config['step'];

        $queryRange = function (string $query) use ($baseUrl, $start, $end, $step) {
            try {
                $res = Http::timeout(5)->get("{$baseUrl}/api/v1/query_range", [
                    'query' => $query,
                    'start' => $start,
                    'end' => $end,
                    'step' => $step,
                ]);
                if ($res->successful() && $res->json('status') === 'success') {
                    return $res->json('data.result') ?: [];
                }
            } catch (\Throwable $e) {
                // ignore
            }
            return [];
        };

        $tpsData = $queryRange("clamp_max(rate(mc_server_tick_seconds_count{$selector}[1m]), 20)");
        $msptData = $queryRange("rate(mc_server_tick_seconds_sum{$selector}[1m]) / rate(mc_server_tick_seconds_count{$selector}[1m]) * 1000");
        $dimMsptData = $queryRange("rate(mc_dimension_tick_seconds_sum{$selector}[1m]) / rate(mc_dimension_tick_seconds_count{$selector}[1m]) * 1000");
        // 安全起見：直接以百分比計算走勢，不洩漏實際 RAM 數值大小
        $heapPercentData = $queryRange("jvm_memory_bytes_used{job=\"{$job}\", area=\"heap\"} / jvm_memory_bytes_max{job=\"{$job}\", area=\"heap\"} * 100");
        $entitiesData = $queryRange("sum(mc_entities_total{$selector})");
        $chunksData = $queryRange("sum(mc_dimension_chunks_loaded{$selector})");
        $playersData = $queryRange("sum(mc_entities_total{job=\"{$job}\", type=\"Player\"}) or vector(0)");

        $formatSeries = function (array $result, ?string $defaultName = null, ?\Closure $labelMapper = null) {
            $seriesList = [];
            foreach ($result as $item) {
                $name = $defaultName;
                if ($labelMapper) {
                    $name = $labelMapper($item['metric'] ?? []);
                }
                $points = [];
                foreach ($item['values'] ?? [] as $val) {
                    $ts = (int) $val[0] * 1000;
                    $num = is_numeric($val[1]) ? (float) $val[1] : 0.0;
                    $points[] = [$ts, round($num, 2)];
                }
                $seriesList[] = [
                    'name' => $name,
                    'points' => $points,
                ];
            }
            return $seriesList;
        };

        $dimSeries = $formatSeries($dimMsptData, 'Dimension', function ($m) {
            $name = $m['name'] ?? 'Unknown';
            return $this->dimensionLabels[$name] ?? ucfirst(str_replace('_', ' ', $name));
        });

        return [
            'status' => 'success',
            'is_mock' => false,
            'start' => $start * 1000,
            'end' => $end * 1000,
            'tps' => $formatSeries($tpsData, 'TPS')[0]['points'] ?? [],
            'mspt' => $formatSeries($msptData, 'MSPT')[0]['points'] ?? [],
            'dimension_mspt' => $dimSeries,
            'heap_percent' => $formatSeries($heapPercentData, '記憶體使用率 (%)')[0]['points'] ?? [],
            'entities' => $formatSeries($entitiesData, '實體數量')[0]['points'] ?? [],
            'chunks' => $formatSeries($chunksData, '載入區塊數')[0]['points'] ?? [],
            'players' => $formatSeries($playersData, '在線玩家數')[0]['points'] ?? [],
        ];
    }

    /**
     * 生成 Mock 即時狀態摘要 (本地開發與版型測試)
     */
    protected function generateMockSummary(): array
    {
        return [
            'status' => 'success',
            'is_mock' => true,
            'server_online' => true,
            'last_updated' => now()->toIso8601String(),
            'metrics' => [
                'tps' => 20.0,
                'mspt' => 2.45,
                'players_online' => 3,
                'chunks_loaded' => 156,
                'entities_total' => 342,
                'heap_percent' => 41.5,
                'cpu_percent' => 18.5,
                'dimensions' => [
                    ['name' => 'overworld', 'label' => '主世界 (Overworld)', 'mspt' => 1.85, 'chunks' => 82],
                    ['name' => 'the_nether', 'label' => '地獄 (The Nether)', 'mspt' => 0.28, 'chunks' => 28],
                    ['name' => 'the_end', 'label' => '終界 (The End)', 'mspt' => 0.12, 'chunks' => 16],
                    ['name' => 'twilight_forest', 'label' => '暮光森林 (Twilight Forest)', 'mspt' => 0.65, 'chunks' => 30],
                ],
            ],
        ];
    }

    /**
     * 生成 Mock 歷史時間序列 (本地開發與版型測試)
     */
    protected function generateMockHistory(string $range, array $config): array
    {
        $end = time();
        $start = $end - $config['seconds'];
        $stepSeconds = match ($range) {
            '1h'  => 30,
            '6h'  => 120,
            '24h' => 600,
            '7d'  => 3600,
            default => 60,
        };

        $tps = [];
        $mspt = [];
        $heapPercent = [];
        $entities = [];
        $chunks = [];
        $players = [];
        $dimOverworld = [];
        $dimNether = [];
        $dimEnd = [];
        $dimTwilight = [];

        $gcPeriod = 1800; // GC 每 30 分鐘一個週期

        for ($t = $start; $t <= $end; $t += $stepSeconds) {
            $ts = $t * 1000;
            $progress = ($t - $start) / max(1, $config['seconds']);

            // TPS: 基本上 20.0，微小抖動
            $tpsVal = 20.0 - (sin($t / 200) > 0.85 ? rand(1, 4) * 0.1 : 0);
            $tps[] = [$ts, round(max(18.0, min(20.0, $tpsVal)), 2)];

            // MSPT: 基礎 2.2ms ~ 4.5ms，偶爾小峰值
            $msptVal = 2.2 + abs(sin($t / 150)) * 1.5 + (rand(0, 100) > 95 ? rand(8, 18) : 0);
            $mspt[] = [$ts, round($msptVal, 2)];

            // 各維度
            $dimOverworld[] = [$ts, round($msptVal * 0.65, 2)];
            $dimTwilight[] = [$ts, round($msptVal * 0.20, 2)];
            $dimNether[] = [$ts, round($msptVal * 0.10, 2)];
            $dimEnd[] = [$ts, round($msptVal * 0.05, 2)];

            // JVM GC 鋸齒波百分比 (30% ~ 55% 間波動)
            $phase = ($t % $gcPeriod) / $gcPeriod;
            $pct = 30.0 + ($phase * 25.0) + (sin($t / 100) * 1.5);
            $heapPercent[] = [$ts, round(max(0.0, min(100.0, $pct)), 1)];

            // 實體與區塊
            $entities[] = [$ts, (int) (320 + sin($progress * 12) * 50 + rand(-10, 10))];
            $chunks[] = [$ts, (int) (150 + cos($progress * 8) * 20 + rand(-5, 5))];

            // 玩家數 (0 ~ 4 人)
            $players[] = [$ts, (int) max(0, min(5, round(2 + sin($progress * 6) * 2)))];
        }

        return [
            'status' => 'success',
            'is_mock' => true,
            'start' => $start * 1000,
            'end' => $end * 1000,
            'tps' => $tps,
            'mspt' => $mspt,
            'dimension_mspt' => [
                ['name' => '主世界 (Overworld)', 'points' => $dimOverworld],
                ['name' => '暮光森林 (Twilight Forest)', 'points' => $dimTwilight],
                ['name' => '地獄 (The Nether)', 'points' => $dimNether],
                ['name' => '終界 (The End)', 'points' => $dimEnd],
            ],
            'heap_percent' => $heapPercent,
            'entities' => $entities,
            'chunks' => $chunks,
            'players' => $players,
        ];
    }
}
