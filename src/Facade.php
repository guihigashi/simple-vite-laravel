<?php

namespace Guihigashi\SimpleVite;

class Facade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return SimpleVite::class;
    }
}