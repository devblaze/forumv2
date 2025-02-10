<?php

namespace App\Console;

class Kernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('posts:cleanup')->daily();
    }
}
