<?php

namespace Database\Seeders;

use App\Models\MotivationalQuote;
use Illuminate\Database\Seeder;

class MotivationalQuoteSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = __DIR__ . '/motivational-quotes.csv';

        if (!file_exists($csvPath)) {
            $this->command->warn('CSV file not found: ' . $csvPath);
            return;
        }

        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle); // Skip header row

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 3) {
                MotivationalQuote::firstOrCreate(
                    ['quote' => $row[1]],
                    ['author' => $row[2], 'is_active' => true]
                );
                $count++;
            }
        }

        fclose($handle);
        $this->command->info("Seeded {$count} motivational quotes.");
    }
}
