<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Typesense\Client;

class DeleteTypesenseCollection extends Command
{
    protected $signature = 'typesense:delete-collection {collection}';
    protected $description = 'Delete a Typesense collection';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $collectionName = $this->argument('collection');

        $typesense = new Client([
            'nodes' => [
                [
                    'host' => env('TYPESENSE_HOST', 'localhost'),
                    'port' => env('TYPESENSE_PORT', '8108'),
                    'protocol' => env('TYPESENSE_PROTOCOL', 'http'),
                ],
            ],
            'api_key' => env('TYPESENSE_API_KEY', ''),
            'connection_timeout_seconds' => 2,
        ]);

        try {
            $typesense->collections[$collectionName]->delete();
            $this->info("Collection '{$collectionName}' deleted successfully.");
        } catch (\Exception $e) {
            $this->error("Failed to delete collection '{$collectionName}': " . $e->getMessage());
        }
    }
}
