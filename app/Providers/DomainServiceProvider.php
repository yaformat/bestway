<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use App\Models\Domain;

class DomainServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Регистрируем синглтон для текущего домена
        $this->app->singleton('current_domain', function () {
            return null;
        });
        
        // Регистрируем helper функцию
        if (!function_exists('domain')) {
            $this->app->bind('domain', function () {
                return app('current_domain');
            });
        }
    }
    
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Шарим переменные домена во все вью
        View::composer('*', function ($view) {
            $domain = app('current_domain');
            
            if ($domain) {
                $view->with([
                    'currentDomain' => $domain,
                    'domainSettings' => $domain->getAllSettingsAttribute(),
                ]);
            }
        });
        
        // Регистрируем директивы Blade
        Blade::directive('domainSetting', function ($key, $default = null) {
            return "<?php echo e(app('current_domain')?->getSetting({$key}, {$default})); ?>";
        });
        
        Blade::directive('domainHas', function ($key) {
            return "<?php if (app('current_domain')?->getSetting({$key}) !== null): ?>";
        });
        
        Blade::directive('endDomainHas', function () {
            return "<?php endif; ?>";
        });
        
        Blade::directive('ifDomain', function ($domain) {
            return "<?php if (app('current_domain')?->domain === {$domain}): ?>";
        });
        
        Blade::directive('endIfDomain', function () {
            return "<?php endif; ?>";
        });
    }
}