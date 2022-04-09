<?php

namespace Guihigashi\SimpleVite;

use Illuminate\Support\Facades\Config;

class Manifest
{
    private mixed $manifest;
    private mixed $main;

    public function __construct(string $manifest)
    {
        $this->manifest = json_decode($manifest, true, 10);

        $input = Config::get('simple-vite.input');

        if (isset($this->manifest[Config::get('simple-vite.input')])) {
            $this->main = $this->manifest[Config::get('simple-vite.input')];
        } else {
            throw new SimpleViteException(sprintf('can\'t find "%s" in manifest.json', $input));
        }
    }

    /**
     * @return string
     * @throws SimpleViteException
     */
    public function file(): string
    {
        if (isset($this->main['file'])) {
            return $this->main['file'];
        } else {
            throw new SimpleViteException('invalid "file" entry');
        }
    }

    public function css(): array
    {
        if (isset($this->main['css'])) {
            return $this->main['css'];
        } else {
            return [];
        }
    }

    public function imports()
    {
        return array_merge($this->entriesFile('imports'), $this->entriesFile('dynamicImports'));
    }

    /**
     * @param string $entry
     * @return array
     * @throws SimpleViteException
     */
    private function entriesFile(string $entry): array
    {
        if (isset($this->main[$entry])) {
            return array_map(function (string $chunk) {
                if (isset($this->manifest[$chunk]) && isset($this->manifest[$chunk]['file'])) {
                    return $this->manifest[$chunk]['file'];
                } else {
                    throw new SimpleViteException(sprintf('invalid %s "file" entry', $chunk));
                }
            }, $this->main[$entry]);
        } else {
            return [];
        }
    }
}
