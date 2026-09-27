<?php

declare(strict_types=1);

use Illuminate\Support\Arr;

if (!function_exists('plugin')) {
    function plugin(string $name)
    {
        return app('plugins')->get($name);
    }
}

if (!function_exists('plugin_assets')) {
    /** @deprecated */
    function plugin_assets(string $name, string $relativeUri): string
    {
        $plugin = plugin($name);
        if ($plugin) {
            return $plugin->assets($relativeUri);
        } else {
            throw new InvalidArgumentException('No such plugin.');
        }
    }
}

if (!function_exists('json')) {
    function json()
    {
        $args = func_get_args();

        if (count($args) === 1 && is_array($args[0])) {
            return response()->json($args[0]);
        } elseif (count($args) === 3 && is_array($args[2])) {
            // The third argument is array of extra fields
            return response()->json([
                'code' => $args[1],
                'message' => $args[0],
                'data' => $args[2],
            ]);
        } else {
            return response()->json([
                'code' => Arr::get($args, 1, 1),
                'message' => $args[0],
            ]);
        }
    }
}

if (!function_exists('option')) {
    /**
     * Get / set the specified option value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     */
    function option(string|array $key, mixed $default = null, bool $raw = false)
    {
        $options = app('options');

        if (is_array($key)) {
            $options->set($key);

            return;
        }

        return $options->get($key, $default, $raw);
    }
}

if (!function_exists('option_localized')) {
    function option_localized($key = null, $default = null, $raw = false)
    {
        return option($key.'_'.config('app.locale'), option($key, $default), $raw);
    }
}

if (!function_exists('markdown')) {
    function markdown(?string $text = null, array $options = []): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        // Dedent: strip common leading whitespace from multiline strings (e.g. from Twig {% apply markdown %})
        $lines = explode("\n", $text);
        $minIndent = null;
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            preg_match('/^[ \t]*/', $line, $matches);
            $indent = strlen($matches[0]);
            if ($minIndent === null || $indent < $minIndent) {
                $minIndent = $indent;
            }
        }

        if ($minIndent !== null && $minIndent > 0) {
            foreach ($lines as $i => $line) {
                if (trim($line) !== '') {
                    $lines[$i] = substr($line, $minIndent);
                }
            }
            $text = implode("\n", $lines);
        }

        return \Illuminate\Support\Str::markdown(trim($text), $options);
    }
}

if (!function_exists('markdown_file')) {
    function markdown_file(string $path, array $options = []): string
    {
        $candidates = [
            resource_path('markdown/'.$path),
            resource_path('markdown/'.$path.'.md'),
            resource_path('content/'.$path),
            resource_path('content/'.$path.'.md'),
            resource_path('views/'.$path),
            resource_path('views/'.$path.'.md'),
            base_path($path),
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_file($candidate)) {
                return markdown(file_get_contents($candidate), $options);
            }
        }

        return '';
    }
}

