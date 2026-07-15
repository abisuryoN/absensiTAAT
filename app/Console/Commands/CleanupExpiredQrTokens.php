<?php

namespace App\Console\Commands;

use App\Services\QrTokenService;
use Illuminate\Console\Command;

class CleanupExpiredQrTokens extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'qr:cleanup';

    /**
     * The console command description.
     */
    protected $description = 'Remove expired and used QR tokens from the database';

    /**
     * Execute the console command.
     */
    public function handle(QrTokenService $service): int
    {
        $deleted = $service->cleanupExpired();

        $this->info("Cleaned up {$deleted} expired/used QR tokens.");

        return Command::SUCCESS;
    }
}
