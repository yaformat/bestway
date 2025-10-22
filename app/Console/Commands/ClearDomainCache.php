<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearDomainCache extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'domain:clear-cache {--all= : Очистить кэш всех доменов}';
    
    /**
     * The console command description.
     */
    protected $description = 'Очищает кэш настроек доменов';
    
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('all')) {
            // Очищаем все кэши доменов
            $domains = Cache::getRedis()->keys('domain.*');
            
            foreach ($domains as $key) {
                Cache::forget($key);
            }
            
            $this->info('Кэш всех доменов очищен');
        } else {
            // Очищаем только кэш домена по умолчанию
            Cache::forget('domain.default');
            $this->info('Кэш домена по умолчанию очищен');
        }
        
        return Command::SUCCESS;
    }
}