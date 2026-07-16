<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LiburApiService
{
    protected string $apiUrl = 'https://libur.deno.dev/api';

    /**
     * Fetch holidays from libur.deno.dev API for a specific year.
     */
    public function fetchHolidays(?int $year = null): array
    {
        try {
            $year = $year ?? date('Y');
            
            Log::info('Fetching holidays from API', ['year' => $year, 'url' => $this->apiUrl]);
            
            $response = Http::timeout(30)
                ->withOptions(['verify' => true])
                ->get($this->apiUrl);

            if (!$response->successful()) {
                Log::error('Failed to fetch holidays from API', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $this->apiUrl
                ]);
                return [];
            }

            $holidays = $response->json();
            
            if (!is_array($holidays)) {
                Log::error('API response is not an array', ['response' => $holidays]);
                return [];
            }
            
            Log::info('API response received', ['count' => count($holidays)]);
            
            // Filter by year if specified
            if ($year) {
                $holidays = array_filter($holidays, function ($holiday) use ($year) {
                    return isset($holiday['date']) && substr($holiday['date'], 0, 4) == $year;
                });
                // Re-index array after filtering
                $holidays = array_values($holidays);
            }

            return $holidays;
        } catch (\Exception $e) {
            Log::error('Error fetching holidays from API: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch raw holidays for a year.
     * Returns null if the API call itself failed, or an array (possibly empty) if the call succeeded.
     */
    public function fetchHolidaysRaw(int $year): ?array
    {
        try {
            Log::info('Fetching holidays from API', ['year' => $year, 'url' => $this->apiUrl]);

            $response = Http::timeout(30)
                ->withOptions(['verify' => true])
                ->get($this->apiUrl);

            if (!$response->successful()) {
                Log::error('Failed to fetch holidays from API', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null; // API call gagal
            }

            $holidays = $response->json();

            if (!is_array($holidays)) {
                Log::error('API response is not an array', ['response' => $holidays]);
                return null; // API call gagal
            }

            Log::info('API response received', ['count' => count($holidays)]);

            $filtered = array_values(array_filter($holidays, function ($holiday) use ($year) {
                return isset($holiday['date']) && substr($holiday['date'], 0, 4) == $year;
            }));

            return $filtered; // sukses, walau bisa saja kosong
        } catch (\Exception $e) {
            Log::error('Error fetching holidays from API: ' . $e->getMessage());
            return null; // API call gagal
        }
    }

    /**
     * Get holiday types mapping.
     */
    public function getHolidayType(bool $isNationalHoliday): string
    {
        return $isNationalHoliday ? 'national' : 'school';
    }

    /**
     * Parse API holiday data to database format.
     */
    public function parseHolidayData(array $apiHoliday): array
    {
        return [
            'name' => $apiHoliday['name'],
            'date' => $apiHoliday['date'],
            'type' => $this->getHolidayType($apiHoliday['is_national_holiday']),
            'description' => $apiHoliday['is_national_holiday'] 
                ? 'Hari libur nasional' 
                : 'Cuti bersama',
        ];
    }
}