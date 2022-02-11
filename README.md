# A Blade directive for Vite/React assets

[![Latest Stable Version](https://poser.pugx.org/guihigashi/simple-vite-laravel/v)](https://packagist.org/packages/guihigashi/simple-vite-laravel)
[![Total Downloads](https://poser.pugx.org/guihigashi/simple-vite-laravel/downloads)](https://packagist.org/packages/guihigashi/simple-vite-laravel)

## Installation

You can install the package via composer:

```bash
composer require guihigashi/simple-vite-laravel
```

## Usage

Include `@simple_vite` in your `app.blade.html` to inject `<script>` and `<link>`
tags based on the value of `APP_ENV` (`production` or else).
If you use `php artisan serve --host=0.0.0.0`, also use `yarn dev --host`.

The defaults are based on this `vite.config.ts`:

```typescript
import react from "@vitejs/plugin-react"
import { defineConfig } from "vite"

export default defineConfig(({ command }) => ({
    base: command === "serve" ? "" : "/dist/",
    publicDir: false,
    build: {
        manifest: true,
        outDir: "public/dist",
        polyfillModulePreload: false,
        rollupOptions: {
            input: "resources/scripts/main.tsx",
        },
    },
    plugins: [react()],
}))

```

If you want to change defaults:

```
php artisan vendor:publish --tag=simple-vite
```
