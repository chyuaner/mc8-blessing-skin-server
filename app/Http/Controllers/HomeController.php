<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;

class HomeController extends Controller
{
    public function index()
    {
        return view('home')
            ->with('user', auth()->user())
            ->with('site_description', option_localized('site_description'))
            ->with('transparent_navbar', (bool) option('transparent_navbar', false))
            ->with('fixed_bg', option('fixed_bg'))
            ->with('hide_intro', option('hide_intro'))
            ->with('is_home', true)
            ->with('home_pic_url', option('home_pic_url') ?: config('options.home_pic_url'));
    }

    public function page(string $slug)
    {
        $slug = trim($slug, '/');
        if (str_contains($slug, '..')) {
            abort(404);
        }

        // 1. 若有建立專屬的獨立 Twig 頁面（例如 pages/{slug}.twig 或 {slug}.twig），優先直接渲染
        $viewSlug = str_replace('/', '.', $slug);
        if (view()->exists("pages.{$viewSlug}")) {
            return view("pages.{$viewSlug}");
        }
        if (view()->exists("page.{$viewSlug}")) {
            return view("page.{$viewSlug}");
        }
        if (view()->exists($viewSlug) && !in_array($viewSlug, ['home', 'mdpage', 'front.base', 'front.navbar', 'front.footer'])) {
            return view($viewSlug);
        }

        // 2. 獨立 .md 檔案共用 mdpage.twig 模板
        $file = resource_path("markdown/{$slug}.md");
        if (!file_exists($file) || !is_file($file)) {
            abort(404);
        }

        $content = file_get_contents($file);
        $title = ucfirst(str_replace(['-', '_', '/'], ' ', basename($slug)));

        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            $title = trim($matches[1]);
        }

        return view('mdpage')
            ->with('page_title', $title)
            ->with('markdown_file_name', $slug);
    }

    public function apiRoot()
    {
        $copyright = Arr::get(
            [
                'Powered with ❤ by Blessing Skin Server.',
                'Powered by Blessing Skin Server.',
                'Proudly powered by Blessing Skin Server.',
                '由 Blessing Skin Server 强力驱动。',
                '采用 Blessing Skin Server 搭建。',
                '使用 Blessing Skin Server 稳定运行。',
                '自豪地采用 Blessing Skin Server。',
            ],
            option_localized('copyright_prefer', 0)
        );

        return response()->json([
            'blessing_skin' => config('app.version'),
            'spec' => 0,
            'copyright' => $copyright,
            'site_name' => option('site_name'),
        ]);
    }
}
