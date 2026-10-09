<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class GenerateAutoindexCommand extends Command
{
    protected $signature = 'dl:generate {--out= : Output directory relative to public}';

    protected $description = 'Generate Nginx autoindex header and footer templates from base.twig';

    public function handle(Filesystem $filesystem)
    {
        // 確保 CLI 環境下能吃對 Blessing Skin 後台設定的網站語言 (預設為繁體中文或讀取資料庫選項)
        app()->setLocale(option('locale') ?: config('app.locale'));

        $this->info('Generating Nginx autoindex templates from base.twig...');

        // 1. 渲染包含完整 base.twig 的視圖
        $html = view('front.dl')->render();

        // 2. 處理黃圈：將登入/會員中心按鈕替換為「回伺服器首頁」
        $homeLink = '<li class="nav-item ml-lg-2"><a class="nav-link nav-btn-login" href="/"><i class="fas fa-house mr-1"></i>回伺服器首頁</a></li>';
        $html = preg_replace(
            '/<li class="nav-item ml-lg-2">\s*<a class="nav-link nav-btn-login"[\s\S]*?<\/li>/u',
            $homeLink,
            $html
        );

        // 3. 標準化本地域名連結，避免上傳至外部伺服器時點擊導向 localhost
        $html = preg_replace('#http://localhost(:\d+)?/#', '/', $html);
        $html = preg_replace('#http://localhost(:\d+)?(?=[\'"])#', '/', $html);
        $html = preg_replace('#http:\\\/\\\/localhost(:\d+)?#', '', $html);

        // 4. 內聯 dl-theme.css 與 dl-theme.js（避免外部路徑 404、Nginx internal 限制或 CDN 快取不同步）
        $cssFile = public_path('autoindex/dl-theme.css');
        if ($filesystem->exists($cssFile)) {
            $cssContent = $filesystem->get($cssFile);
            $inlineCss = "<style id=\"dl-theme-style\">\n{$cssContent}\n</style>";
            $html = preg_replace_callback(
                '/<link[^>]+href=["\'][^"\']*dl-theme\.css["\'][^>]*>/i',
                fn() => $inlineCss,
                $html
            );
        }

        $jsFile = public_path('autoindex/dl-theme.js');
        if ($filesystem->exists($jsFile)) {
            $jsContent = $filesystem->get($jsFile);
            $inlineJs = "<script id=\"dl-theme-script\">\n{$jsContent}\n</script>";
            $html = preg_replace_callback(
                '/<script[^>]+src=["\'][^"\']*dl-theme\.js["\'][^>]*><\/script>/i',
                fn() => $inlineJs,
                $html
            );
        }

        // 5. 根據切分標記拆分出 header.html 與 footer.html
        $splitMarker = '<!-- ###NGINX_AUTOINDEX_CONTENT_SPLIT### -->';
        if (!str_contains($html, $splitMarker)) {
            $this->error('Split marker not found in rendered view.');
            return 1;
        }

        [$header, $footer] = explode($splitMarker, $html, 2);

        // 6. 輸出至目標目錄 (預設 public/autoindex)
        $outDirName = $this->option('out') ?: 'autoindex';
        $outPath = public_path($outDirName);
        $filesystem->ensureDirectoryExists($outPath);

        $headerPath = $outPath.'/header.html';
        $footerPath = $outPath.'/footer.html';

        $filesystem->put($headerPath, trim($header).PHP_EOL);
        $filesystem->put($footerPath, PHP_EOL.trim($footer).PHP_EOL);

        // 7. 同步鏡像至 public/dl/.theme/
        $legacyPath = public_path('dl/.theme');
        $filesystem->ensureDirectoryExists($legacyPath);
        $filesystem->put($legacyPath.'/header.html', trim($header).PHP_EOL);
        $filesystem->put($legacyPath.'/footer.html', PHP_EOL.trim($footer).PHP_EOL);

        // 同步鏡像實體 css 與 js 檔案備用
        if ($filesystem->exists($cssFile)) {
            $filesystem->copy($cssFile, $legacyPath.'/dl-theme.css');
        }
        if ($filesystem->exists($jsFile)) {
            $filesystem->copy($jsFile, $legacyPath.'/dl-theme.js');
        }
        $nginxConf = public_path('autoindex/nginx.conf.example');
        if ($filesystem->exists($nginxConf)) {
            $filesystem->copy($nginxConf, $legacyPath.'/nginx.conf.example');
        }

        $this->info("✓ Generated: {$headerPath}");
        $this->info("✓ Generated: {$footerPath}");
        $this->info("✓ Mirrored:  {$legacyPath}/header.html (Self-contained inline CSS/JS)");
        $this->info("✓ Mirrored:  {$legacyPath}/footer.html (Self-contained inline CSS/JS)");
        $this->info("✓ Mirrored:  {$legacyPath}/dl-theme.css");
        $this->info("✓ Mirrored:  {$legacyPath}/dl-theme.js");
        $this->info('Templates successfully generated and ready for Nginx add_before_body / add_after_body!');

        return 0;
    }
}
