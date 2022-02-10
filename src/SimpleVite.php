<?php

namespace Guihigashi\SimpleVite;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;

class SimpleVite
{
    private $base;
    private $input;
    private $integrity;

    public function __construct()
    {
        $this->base = Config::get('simple-vite.base');
        $this->input = Config::get('simple-vite.input');
        $this->integrity = Config::get('simple-vite.integrity');
    }

    public function config(): HtmlString
    {
        return App::environment('production') ? $this->productionConfig() : $this->localConfig();
    }

    private function localConfig(): HtmlString
    {
        $host = sprintf('http://%s:3000', parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST));

        return new HtmlString(ltrim(<<<HTML
            <script type="module">
                import RefreshRuntime from "$host/@react-refresh"
                RefreshRuntime.injectIntoGlobalHook(window)
                window.\$RefreshReg$ = () => {}
                window.\$RefreshSig$ = () => (type) => type
                window.__vite_plugin_react_preamble_installed__ = true
            </script>
            <script type="module" src="$host/@vite/client"></script>
            <script type="module" src="$host/$this->input"></script>
        HTML
        ));
    }

    private function productionConfig(): HtmlString
    {
        $manifest = json_decode(File::get($this->publicPath('manifest.json')), true);

        $main = $manifest[$this->input];

        $imports = array_map(function (string $import) use ($manifest) {
            return $this->link("modulepreload", $manifest[$import]['file']);
        }, $main['imports']);

        $styles = array_map(function (string $file) {
            return $this->link('stylesheet', $file);
        }, $main['css']);

        return new HtmlString(implode("\n\t", array_merge(
            [$this->link('modulepreload', $main['file'])],
            $imports,
            $styles,
            [$this->script($main['file'])]
        )));
    }

    private function url(string $file): string
    {
        return sprintf('/%s/%s', $this->base, $file);
    }

    private function publicPath(string $file): string
    {
        return App::basePath(implode(DIRECTORY_SEPARATOR, ['public', $this->base, $file]));
    }

    private function integrity(string $file): string
    {
        return sprintf('%s-%s',
            $this->integrity['algo'],
            base64_encode(hash_file($this->integrity['algo'], $this->publicPath($file), true))
        );
    }

    private function script(string $file): string
    {
        return sprintf('<script type="module" src="%s"></script>', $this->url($file));
    }

    private function link(string $rel, string $file): string
    {
        if ($this->integrity['enabled']) {
            return sprintf('<link rel="%s" href="%s" integrity="%s">',
                $rel,
                $this->url($file),
                $this->integrity($file),
            );
        }

        return sprintf('<link rel="%s" href="%s">', $rel, $this->url($file));
    }
}

