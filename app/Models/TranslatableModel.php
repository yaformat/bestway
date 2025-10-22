<?php

namespace App\Models;

use Astrotomic\Translatable\Translatable;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use App\Traits\HasTranslation;
use Illuminate\Support\Arr;

use App\Models\BaseModel;

class TranslatableModel extends BaseModel implements TranslatableContract
{
    use Translatable, HasTranslation;

    public $useTranslationFallback = true;

    public function getFallbackLocale(): string
    {
        if (\Auth::check() && \Auth::user()->fallback_locale) {
            $userLocale = \Auth::user()->fallback_locale;
            if (array_key_exists($userLocale, $this->getTranslationsArray())) {
                return $userLocale;
            }
        }

        $translationsArray = $this->getTranslationsArray();
        $firstAvailableLocale = Arr::first(array_keys($translationsArray));

        return $firstAvailableLocale ?? config('translatable.fallback_locale');
    }

    // public function toArray()
    // {
    //     $array = parent::toArray();
    
    //     // Удаляем ключ 'translations', если он есть
    //     if(array_key_exists('translations', $array)) {
    //         unset($array['translations']);
    //     }
    
    //     return $array;
    // }

    // public function toArrayWithTranslations()
    // {
    //     return array_merge($this->toArray(), ['translations' => $this->getTranslationsArray()]);
    // }
}