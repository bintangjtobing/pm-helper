<?php

namespace Database\Seeders;

use App\Models\BirthdayWish;
use Illuminate\Database\Seeder;

class BirthdayWishSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = __DIR__ . '/birthday-wishes.csv';
        if (!file_exists($csvPath)) return;

        $handle = fopen($csvPath, 'r');
        fgetcsv($handle); // Skip header
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 2 && !empty($row[1])) {
                BirthdayWish::updateOrCreate(
                    ['wish' => $row[1]],
                    ['tone' => $row[2] ?? null, 'audience' => $row[3] ?? 'Universal', 'is_active' => true]
                );
                $count++;
            }
        }

        fclose($handle);
        $this->command->info("Seeded {$count} birthday wishes.");
    }
}
