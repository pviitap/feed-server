<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\Concurrency;

use Illuminate\Console\Command;
use App\Models\News;
use App\Models\Teletext;
use App\Models\Event;
use App\Models\Posting;

class update extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        [$r1, $r2, $r3, $r4] = Concurrency::run([
            fn () => Posting::updateDB(),
            fn () => Event::updateDB(),
            fn () => TeleText::updateDB(),
            fn () => News::updateDB()
        ]);
    }
}
