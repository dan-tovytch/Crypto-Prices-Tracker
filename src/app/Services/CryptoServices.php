<?php

namespace App\Services;

use App\Models\CryptoPrices;
use App\Models\Cryptos;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CryptoServices
{
    protected string $apiUrl;
    protected string $apiToken;

    public function __construct()
    {
        $this->apiUrl   = config('services.coingecko.url');
        $this->apiToken = config('services.coingecko.token');
    }

    public function updateCryptoPrices(): array
    {
        Log::info("Fetching crypto prices from API...");

        $response = $this->fetchCryptoPrices();

        if (!$response) {
            Log::warning("Failed to fetch crypto prices.");
            return [];
        }

        try {
            return $this->processCryptoPrices($response);
        } catch (Exception $e) {
            Log::error("Failed to process crypto prices: " . $e->getMessage());
            throw $e;
        }
    }

    protected function fetchCryptoPrices(): ?array
    {
        $response = Http::withToken($this->apiToken)
            ->get($this->apiUrl);

        if ($response->successful()) {
            return $response->json();
        }

        Log::warning("API returned status code: " . $response->status());
        return null;
    }

    protected function processCryptoPrices(array $data): array
    {
        Log::info("Processing " . count($data) . " crypto prices...");

        $symbols = array_column($data, 'symbol');

        DB::transaction(function () use ($data, $symbols) {
            Cryptos::upsert(
                array_map(fn($crypto) => [
                    'symbol' => $crypto['symbol'],
                    'name'   => $crypto['name'],
                ], $data),
                uniqueBy: ['symbol'],
                update: ['name']
            );

            $cryptoIds = Cryptos::whereIn('symbol', $symbols)
                ->pluck('id', 'symbol');

            $priceRecords = array_map(fn($crypto) => [
                'coin_id'                       => $cryptoIds[$crypto['symbol']],
                'symbol'                        => $crypto['symbol'],
                'name'                          => $crypto['name'],
                'image'                         => $crypto['image'] ?? null,
                'current_price'                 => $crypto['current_price'],
                'market_cap'                    => $crypto['market_cap'],
                'market_cap_rank'               => $crypto['market_cap_rank'] ?? null,
                'total_volume'                  => $crypto['total_volume'],
                'high_24h'                      => $crypto['high_24h'],
                'low_24h'                       => $crypto['low_24h'],
                'price_change_percentage_1h'    => $crypto['price_change_percentage_1h'] ?? null,
                'price_change_percentage_24h'   => $crypto['price_change_percentage_24h'] ?? null,
                'last_updated'                  => now(),
            ], $data);

            CryptoPrices::upsert(
                $priceRecords,
                uniqueBy: ['coin_id'],
                update: [
                    'current_price',
                    'market_cap',
                    'market_cap_rank',
                    'total_volume',
                    'high_24h',
                    'low_24h',
                    'price_change_percentage_1h',
                    'price_change_percentage_24h',
                    'last_updated',
                ]
            );
        });

        $updated = CryptoPrices::whereIn('symbol', $symbols)->get();


        Log::info("Crypto prices processed successfully.");
        return $updated->toArray();
    }
}
