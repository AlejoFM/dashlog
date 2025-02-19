<?php

namespace DashLog\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Elastic\Elasticsearch\ClientBuilder;

class SetupElasticsearchCommand extends Command
{
    protected $signature = 'dashlog:setup-elasticsearch';
    protected $description = 'Setup Elasticsearch index and mapping';

    public function handle()
    {
        try {
            $client = ClientBuilder::create()
                ->setHosts(['localhost:9200'])  // Simplificado a una string simple
                ->build();

            if (!$client->ping()) {
                $this->error('Could not connect to Elasticsearch. Is it running?');
                return Command::FAILURE;
            }

            $this->info('Creating index and mapping...');

            $client->indices()->create([
                'index' => 'request_logs',
                'body' => [
                    'mappings' => [
                        'properties' => [
                            'method' => ['type' => 'keyword'],
                            'url' => ['type' => 'text'],
                            'path' => ['type' => 'keyword'],
                            'status_code' => ['type' => 'integer'],
                            'duration' => ['type' => 'float'],
                            'headers' => ['type' => 'object'],
                            'request' => ['type' => 'object'],
                            'response' => ['type' => 'object'],
                            'ip' => ['type' => 'ip'],
                            'user_agent' => ['type' => 'text'],
                            'user_id' => ['type' => 'keyword'],
                            'created_at' => ['type' => 'date']
                        ]
                    ]
                ]
            ]);

            $this->info('Elasticsearch setup completed successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}