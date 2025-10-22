<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Domain;
use App\Models\DomainSetting;

class DomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем домены для разных стран
        $domains = [
            [
                'name' => 'Bestway Кыргызстан',
                'domain' => 'bestway.site',
                'description' => 'Основной домен для Кыргызстана',
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
                'settings' => [
                    'seo_title' => ['value' => 'Bestway - Туристические туры по Кыргызстану', 'type' => 'string'],
                    'seo_description' => ['value' => 'Лучшие туры, экскурсии и отдых в Кыргызстане. Бронирование отелей и трансферы.', 'type' => 'string'],
                    'seo_keywords' => ['value' => 'туры Кыргызстан, экскурсии Бишкек, отдых Иссык-Куль, отели Кыргызстан', 'type' => 'string'],
                    'default_currency' => ['value' => 'KGS', 'type' => 'string'],
                    'contact_phone' => ['value' => '+996 (312) 123-456', 'type' => 'string'],
                    'contact_email' => ['value' => 'info@bestway.site', 'type' => 'string'],
                    'head_code' => ['value' => '<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="yandex-verification" content="xxxxxxxxxx" />', 'type' => 'string'],
                    'body_open_code' => ['value' => '<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(XXXXXXXXX, "init", {clickmap:true,trackLinks:true,accurateTrackBounce:true});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/XXXXXXXXX" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->', 'type' => 'string'],
                    'body_close_code' => ['value' => '', 'type' => 'string'],
                    'theme' => ['value' => 'bestway-kg', 'type' => 'string'],
                    'language' => ['value' => 'ru', 'type' => 'string'],
                    'timezone' => ['value' => 'Asia/Bishkek', 'type' => 'string'],
                    'google_analytics' => ['value' => 'GA-XXXXXXXXX', 'type' => 'string'],
                    'yandex_metrica' => ['value' => 'XXXXXXXXX', 'type' => 'string'],
                    'maintenance_mode' => ['value' => false, 'type' => 'boolean'],
                    'maintenance_message' => ['value' => 'Сайт на техническом обслуживании. Пожалуйста, зайдите позже.', 'type' => 'string'],
                ],
            ],
            [
                'name' => 'Bestway Казахстан',
                'domain' => 'kz.bestway.site',
                'description' => 'Домен для Казахстана',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 2,
                'settings' => [
                    'seo_title' => ['value' => 'Bestway - Туристические туры по Казахстану', 'type' => 'string'],
                    'seo_description' => ['value' => 'Лучшие туры, экскурсии и отдых в Казахстане. Бронирование отелей и трансферы.', 'type' => 'string'],
                    'seo_keywords' => ['value' => 'туры Казахстан, экскурсии Алматы, отдых Боровое, отели Казахстан', 'type' => 'string'],
                    'default_currency' => ['value' => 'KZT', 'type' => 'string'],
                    'contact_phone' => ['value' => '+7 (727) 123-456', 'type' => 'string'],
                    'contact_email' => ['value' => 'info@kz.bestway.site', 'type' => 'string'],
                    'head_code' => ['value' => '<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="yandex-verification" content="xxxxxxxxxx" />', 'type' => 'string'],
                    'body_open_code' => ['value' => '<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(YYYYYYYYY, "init", {clickmap:true,trackLinks:true,accurateTrackBounce:true});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/YYYYYYYYY" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->', 'type' => 'string'],
                    'body_close_code' => ['value' => '', 'type' => 'string'],
                    'theme' => ['value' => 'bestway-kz', 'type' => 'string'],
                    'language' => ['value' => 'ru', 'type' => 'string'],
                    'timezone' => ['value' => 'Asia/Almaty', 'type' => 'string'],
                    'google_analytics' => ['value' => 'GA-YYYYYYYYY', 'type' => 'string'],
                    'yandex_metrica' => ['value' => 'YYYYYYYYY', 'type' => 'string'],
                    'maintenance_mode' => ['value' => false, 'type' => 'boolean'],
                    'maintenance_message' => ['value' => 'Сайт на техническом обслуживании. Пожалуйста, зайдите позже.', 'type' => 'string'],
                ],
            ],
            [
                'name' => 'Bestway Узбекистан',
                'domain' => 'uz.bestway.site',
                'description' => 'Домен для Узбекистана',
                'is_default' => false,
                'is_active' => true,
                'sort_order' => 3,
                'settings' => [
                    'seo_title' => ['value' => 'Bestway - Туристические туры по Узбекистану', 'type' => 'string'],
                    'seo_description' => ['value' => 'Лучшие туры, экскурсии и отдых в Узбекистане. Бронирование отелей и трансферы.', 'type' => 'string'],
                    'seo_keywords' => ['value' => 'туры Узбекистан, экскурсии Ташкент, отдых Самарканд, отели Узбекистан', 'type' => 'string'],
                    'default_currency' => ['value' => 'UZS', 'type' => 'string'],
                    'contact_phone' => ['value' => '+998 (71) 123-456', 'type' => 'string'],
                    'contact_email' => ['value' => 'info@uz.bestway.site', 'type' => 'string'],
                    'head_code' => ['value' => '<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="yandex-verification" content="xxxxxxxxxx" />', 'type' => 'string'],
                    'body_open_code' => ['value' => '<!-- Yandex.Metrika counter -->
<script type="text/javascript" >
   (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
   m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})
   (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");

   ym(ZZZZZZZZZ, "init", {clickmap:true,trackLinks:true,accurateTrackBounce:true});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/ZZZZZZZZZ" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->', 'type' => 'string'],
                    'body_close_code' => ['value' => '', 'type' => 'string'],
                    'theme' => ['value' => 'bestway-uz', 'type' => 'string'],
                    'language' => ['value' => 'ru', 'type' => 'string'],
                    'timezone' => ['value' => 'Asia/Tashkent', 'type' => 'string'],
                    'google_analytics' => ['value' => 'GA-ZZZZZZZZZ', 'type' => 'string'],
                    'yandex_metrica' => ['value' => 'ZZZZZZZZZ', 'type' => 'string'],
                    'maintenance_mode' => ['value' => false, 'type' => 'boolean'],
                    'maintenance_message' => ['value' => 'Сайт на техническом обслуживании. Пожалуйста, зайдите позже.', 'type' => 'string'],
                ],
            ],
        ];

        foreach ($domains as $domainData) {
            $settings = $domainData['settings'];
            unset($domainData['settings']);
            
            $domain = Domain::create($domainData);
            
            // Создаем настройки
            foreach ($settings as $key => $settingData) {
                $domain->settings()->create([
                    'key' => $key,
                    'value' => $settingData['value'],
                    'type' => $settingData['type'],
                    'description' => DomainSetting::getKeyDescription($key),
                    'is_public' => in_array($key, [
                        'default_currency', 
                        'contact_phone', 
                        'contact_email', 
                        'theme', 
                        'language',
                        'seo_title',
                        'seo_description',
                        'seo_keywords'
                    ]),
                ]);
            }
        }
    }
}