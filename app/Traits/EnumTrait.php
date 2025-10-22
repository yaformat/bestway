<?php
  
namespace App\Traits;

trait EnumTrait 
{
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options() : array 
    {
        $langKeys = static::langKeys();

        $options = [];
        foreach (static::cases() as $case) {
            if (isset($langKeys[0])) {
                $options[$case->value][] = __('enums.'.$langKeys[0].'.'.$case->value);
            }
            if (isset($langKeys[1])) {
                $options[$case->value][] = __('enums.'.$langKeys[1].'.'.$case->value);
            }
        }
        return $options;
    }

    public static function toArray(): array
    {
        $array = [];
        foreach (static::cases() as $case) {
            $array[$case->value] = ['label' => $case->getLabel(), 'label_full' => $case->getLabelFull()];
        }
        return $array;
    }

    public function getLabel(): string
    {
        return static::options()[$this->value][0];
    }

    public function getLabelFull(): string
    {
        return static::options()[$this->value][1] ?? static::options()[$this->value][0];
    }
}