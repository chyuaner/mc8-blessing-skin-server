<?php

namespace Tests;

class StatsControllerTest extends TestCase
{
    public function testWhenDisabledReturns404()
    {
        config(['services.prometheus.enabled' => false]);

        $this->get('/page/stats')->assertStatus(404);
        $this->get('/stats')->assertStatus(404);
        $this->get('/api/stats/summary')->assertStatus(404);
        $this->get('/api/stats/history')->assertStatus(404);

        $this->get('/')->assertDontSee('伺服器效能狀況');
    }

    public function testWhenMockEnabledReturnsDataAndRendersNavbar()
    {
        config(['services.prometheus.enabled' => 'mock']);

        $this->get('/page/stats')
            ->assertStatus(200)
            ->assertSee('伺服器效能狀況')
            ->assertSee('Mock 測試模式');

        $this->get('/stats')->assertStatus(200);

        $this->get('/api/stats/summary')
            ->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'is_mock' => true,
                'server_online' => true,
                'metrics' => [
                    'tps' => 20.0,
                    'players_online' => 3,
                ],
            ]);

        $this->get('/api/stats/history?range=1h')
            ->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'is_mock' => true,
            ])
            ->assertJsonStructure([
                'status',
                'is_mock',
                'tps',
                'mspt',
                'dimension_mspt',
                'heap_percent',
                'entities',
                'chunks',
                'players',
            ]);

        // 確保沒有洩漏實體記憶體大小的欄位
        $response = $this->get('/api/stats/summary');
        $response->assertJsonMissing(['heap_used_bytes', 'heap_max_bytes']);

        $this->get('/')->assertSee('伺服器效能狀況');
    }
}
