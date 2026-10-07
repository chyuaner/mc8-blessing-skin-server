<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GuestPageCache
{
    /**
     * Handle an incoming request and attach public cache headers for guests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $ttl  Cache lifetime in seconds (default: 600)
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, int $ttl = 600)
    {
        /** @var Response $response */
        $response = $next($request);

        // 僅針對安全且成功的 GET / HEAD 請求處理快取標頭
        if (!$request->isMethodSafe() || $response->getStatusCode() !== 200) {
            return $response;
        }

        // 若有 Session 錯誤或狀態提示訊息（如表單驗證失敗、重導向訊息），不快取
        if ($request->hasSession() && ($request->session()->has('errors') || $request->session()->has('status'))) {
            $response->headers->set('Cache-Control', 'no-cache, private, no-store');
            $response->headers->set('X-Accel-Expires', '0');
            return $response;
        }

        // 如果是已登入會員：明確告知反向代理與瀏覽器「絕對不可快取」，確保動態會員選單與狀態即時更新
        if (auth()->check()) {
            $response->headers->set('Cache-Control', 'no-cache, private, no-store');
            $response->headers->set('X-Accel-Expires', '0');
            return $response;
        }

        // 如果是未登入訪客：
        // 1. X-Accel-Expires: 告知 Nginx FastCGI 快取有效秒數
        // 2. Cache-Control: 告知公用中繼快取 (CDN / 反向代理) 可以快取
        $response->headers->set('Cache-Control', "public, max-age={$ttl}, s-maxage={$ttl}");
        $response->headers->set('X-Accel-Expires', (string) $ttl);

        return $response;
    }
}
