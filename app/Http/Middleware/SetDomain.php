<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Domain;
use Symfony\Component\HttpFoundation\Response;

class SetDomain
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        
        // Получаем домен из БД или кэша
        $domain = $this->getDomain($host);
        
        // Если домен не найден, пробуем домен по умолчанию
        if (!$domain) {
            $domain = $this->getDefaultDomain();
        }
        
        // Если и домен по умолчанию не найден, используем базовый
        if (!$domain) {
            $domain = $this->createFallbackDomain($host);
        }
        
        // Устанавливаем домен в сервис-контейнер
        app()->instance('current_domain', $domain);
        
        // Устанавливаем конфигурацию домена
        $this->setDomainConfig($domain);
        
        // Проверяем режим обслуживания
        if ($domain->getSetting('maintenance_mode', false)) {
            if (!$request->is('admin/*') && !$request->is('api/admin/*')) {
                return response()->view('errors.maintenance', [
                    'message' => $domain->getSetting('maintenance_message', 'Сайт на техническом обслуживании')
                ], 503);
            }
        }
        
        return $next($request);
    }
    
    /**
     * Получает домен из БД или кэша
     */
    protected function getDomain(string $host): ?Domain
    {
        $cacheKey = "domain.{$host}";
        $ttl = config('domains.cache_ttl', 60);
        
        return Cache::remember($cacheKey, $ttl, function () use ($host) {
            return Domain::with('settings')
                        ->where('domain', $host)
                        ->where('is_active', true)
                        ->first();
        });
    }
    
    /**
     * Получает домен по умолчанию
     */
    protected function getDefaultDomain(): ?Domain
    {
        $cacheKey = 'domain.default';
        $ttl = config('domains.cache_ttl', 60);
        
        return Cache::remember($cacheKey, $ttl, function () {
            return Domain::with('settings')
                        ->where('is_default', true)
                        ->where('is_active', true)
                        ->first();
        });
    }
    
    /**
     * Создает временный домен, если ничего не найдено
     */
    protected function createFallbackDomain(string $host): Domain
    {
        $domain = new Domain([
            'name' => 'Fallback Domain',
            'domain' => $host,
            'description' => 'Автоматически созданный домен',
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 999,
        ]);
        
        // Добавляем базовые настройки
        $domain->setRelation('settings', collect());
        
        return $domain;
    }
    
    /**
     * Устанавливает конфигурацию для домена
     */
    protected function setDomainConfig(Domain $domain): void
    {
        // Устанавливаем основные настройки
        config([
            'app.name' => $domain->name,
            'app.url' => $domain->isCurrent() ? $domain->getBaseUrl() : config('app.url'),
            'app.timezone' => $domain->getSetting('timezone', config('app.timezone')),
            'app.locale' => $domain->getSetting('language', config('app.locale')),
            'app.currency' => $domain->getSetting('default_currency', 'USD'),
        ]);
        
        // Устанавливаем настройки в глобальную переменную для удобного доступа
        config(['domain_settings' => $domain->getAllSettingsAttribute()]);
        
        // Устанавливаем тему
        if ($theme = $domain->getSetting('theme')) {
            config(['themes.active' => $theme]);
        }
    }
}