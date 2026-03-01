<?php

namespace App\Jobs;

use App\Events\CryptoPriceUpdated;
use App\Services\CryptoServices;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessCrypto implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;


    /**
     * Execute the job.
     */
    public function handle(CryptoServices $service): void
    {
        $coins = $service->updateCryptoPrices();

        foreach ($coins as $coin) {
            CryptoPriceUpdated::dispatch($coin);
        }
    }
}
