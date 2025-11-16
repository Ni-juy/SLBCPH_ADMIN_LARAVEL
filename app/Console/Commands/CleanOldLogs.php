<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SystemLogsController;

class CleanOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clean-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean logs older than 90 days from system.log';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $logController = new SystemLogsController();
        $logController->cleanOldLogs();

        $this->info('Old logs cleaned successfully.');

        return 0;
    }
}
