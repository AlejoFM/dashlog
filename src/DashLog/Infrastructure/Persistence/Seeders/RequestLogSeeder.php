<?php

namespace DashLog\Infrastructure\Persistence\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class RequestLogSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        
        $methods = ['GET', 'POST', 'PUT', 'DELETE'];
        $statusCodes = [200, 201, 400, 401, 403, 404, 500];

        for ($i = 0; $i < 50; $i++) {
            $path = '/' . $faker->word;
            $headers = [
                'accept' => 'application/json',
                'user-agent' => $faker->userAgent,
                'accept-language' => 'en-US,en;q=0.9'
            ];

            DB::table('request_logs')->insert([
                'method' => $faker->randomElement($methods),
                'url' => 'https://example.com' . $path,
                'path' => $path,
                'status_code' => $faker->randomElement($statusCodes),
                'duration' => $faker->randomFloat(2, 0.1, 2.0),
                'headers' => json_encode($headers),
                'request' => json_encode(['param' => $faker->word]),
                'response' => json_encode(['message' => $faker->sentence]),
                'ip' => $faker->ipv4,
                'user_agent' => $faker->userAgent,
                'user_id' => $faker->optional()->numberBetween(1, 10),
                'created_at' => $faker->dateTimeThisMonth(),
                'updated_at' => $faker->dateTimeThisMonth()
            ]);
        }
    }
} 