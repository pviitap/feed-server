<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\News;

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
        News::updateDBFromSources();
    }
}
