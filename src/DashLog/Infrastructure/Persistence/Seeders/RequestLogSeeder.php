<?php

namespace AledDev\DashLog\Infrastructure\Persistence\Seeders;

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
                'id' => Str::uuid(),
                'method' => $faker->randomElement($methods),
                'url' => 'https://example.com' . $path,
                'status_code' => $faker->randomElement($statusCodes),
                'duration' => $faker->randomFloat(2, 0.1, 2.0),
                'headers' => json_encode($headers),
                'request' => json_encode(value: ['param' => $faker->word]),
                'response' => json_encode(['message' => $faker->sentence]),
                'cookies' => json_encode(['name' => $faker->word, 'value' => $faker->word]),
                'session' => json_encode(['name' => $faker->word, 'value' => $faker->word]),
                'stack_trace' => json_encode([
                    'message' => 'Undefined property: App\Models\User::$settings',
                    'exception' => 'ErrorException',
                    'file' => '/var/www/html/app/Models/User.php',
                    'line' => 45,
                    'trace' => [
                        [
                            'file' => '/var/www/html/app/Http/Controllers/UserController.php',
                            'line' => 28,
                            'function' => 'getSettings',
                            'class' => 'App\Models\User'
                        ],
                        [
                            'file' => '/var/www/html/vendor/laravel/framework/src/Illuminate/Routing/Controller.php',
                            'line' => 54,
                            'function' => 'show',
                            'class' => 'App\Http\Controllers\UserController'
                        ],
                        [
                            'file' => '/var/www/html/vendor/laravel/framework/src/Illuminate/Routing/Route.php',
                            'line' => 261,
                            'function' => 'callAction',
                            'class' => 'Illuminate\Routing\Controller'
                        ]
                    ]
                ]),
                'ip' => $faker->ipv4,
                'user_agent' => $faker->userAgent,
                'user_id' => $faker->numberBetween(1, 10),
                'created_at' => $faker->dateTimeThisMonth(),
                'updated_at' => $faker->dateTimeThisMonth()
            ]);
        }
    }
} 