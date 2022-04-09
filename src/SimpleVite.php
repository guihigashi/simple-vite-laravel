<?php

namespace Guihigashi\SimpleVite;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;

class SimpleVite
{
    public function tags(): HtmlString
    {
        return App::environment('production') ? $this->productionTags() : $this->localTags();
    }

    private function localTags(): HtmlString
    {
        $host = sprintf('http://%s:%s',
            parse_url($_SERVER['HTTP_HOST'], PHP_URL_HOST),
            Config::get('simple-vite.dev_server_port')
        );
        $input = Config::get('simple-vite.input');

        return new HtmlString(ltrim(<<<HTML
            <script type="module">
                import RefreshRuntime from "$host/@react-refresh"
                RefreshRuntime.injectIntoGlobalHook(window)
                window.\$RefreshReg$ = () => {}
                window.\$RefreshSig$ = () => (type) => type
                window.__vite_plugin_react_preamble_installed__ = true
            </script>
            <script type="module" src="$host/@vite/client"></script>
            <script type="module" src="$host/$input"></script>
        HTML
        ));
    }

    private function productionTags(): HtmlString
    {
        $manifest = new Manifest(File::get($this->outDirPath('manifest.json')));

        $imports = array_map(fn($f) => $this->link("modulepreload", $f), $manifest->imports());

        $styles = array_map(fn($f) => $this->link('stylesheet', $f), $manifest->css());

        return new HtmlString(implode("\n\t", array_merge(
            [$this->link('modulepreload', $manifest->file())],
            $imports,
            $styles,
            [$this->script($manifest->file())]
        )));
    }

    private function url(string $file): string
    {
        return sprintf('/%s/%s', Config::get('simple-vite.base'), $file);
    }

    private function outDirPath(string $file): string
    {
        return implode(DIRECTORY_SEPARATOR, [App::make('path.public'), Config::get('simple-vite.base'), $file]);
    }

    private function integrity(string $algo, string $file): string
    {
        return sprintf('%s-%s',
            $algo,
            base64_encode(hash_file($algo, $this->outDirPath($file), true))
        );
    }

    private function script(string $file): string
    {
        return sprintf('<script type="module" src="%s"></script>', $this->url($file));
    }

    private function link(string $rel, string $file): string
    {
        $integrity = Config::get('simple-vite.integrity');

        if ($integrity['enabled']) {
            return sprintf('<link rel="%s" href="%s" integrity="%s">',
                $rel,
                $this->url($file),
                $this->integrity($integrity['algo'], $file),
            );
        }

        return sprintf('<link rel="%s" href="%s">', $rel, $this->url($file));
    }
}
