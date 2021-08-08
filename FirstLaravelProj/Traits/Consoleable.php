<?php

namespace App\Libraries\Traits;

trait Consoleable
{
    public static function showConsoleInfoMsg(string $msg, mixed $console = null): void
    {
        if (isset($console)) {
            $console->info($msg);
        }
    }
}
