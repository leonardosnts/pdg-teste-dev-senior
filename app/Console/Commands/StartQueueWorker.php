<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StartQueueWorker extends Command
{
    protected $signature = 'ingestion:worker {--workers=2}';


    protected $description = 'Start optimized queue workers for sample ingestion';


    public function handle()
    {
        $workers = $this->option('workers');

        $this->info("Starting {$workers} queue workers for sample ingestion...");
        $this->info("Queue: sample-ingestion");
        $this->info("Connection: redis");
        $this->info("Memory limit: 512MB");
        $this->info("Timeout: 600s");
        $this->newLine();

        $this->call('queue:work', [
            'connection' => 'redis',
            '--queue' => 'sample-ingestion',
            '--sleep' => 3,
            '--tries' => 3,
            '--timeout' => 600,
            '--memory' => 512,
            '--max-jobs' => 100,
        ]);
    }
}
