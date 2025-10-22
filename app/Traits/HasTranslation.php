<?php

namespace App\Traits;

use Auth;
use Illuminate\Support\Facades\App;

trait HasTranslation
{
    public function getUseTranslationFallback()
    {
        // Assumption: Auth::check() is true
        $userFallbackLocale = Auth::user()->fallback_locale;
        
        // Check if the fallback_locale is same as app's current locale
        return App::getLocale() === $userFallbackLocale;
    }

}