<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HeartbeatService;

class RunHeartbeat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autopulse:heartbeat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the core heartbeat engine';

    /**
     * Execute the console command.
     */
    public function handle(HeartbeatService $heartbeat): int
    {
        $this->info('Starting heartbeat...');
        $heartbeat->tick();
        $this->info('Heartbeat complete.');
        
        return Command::SUCCESS;
    }
}
