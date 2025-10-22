<?php
namespace App\Factories;

class SortingsFactory
{
    public static function create($sortingsProvider, $selected = null)
    {
        if (!isset($sortingsProvider) || !method_exists($sortingsProvider, 'sortings')) {
            return [];
        }

        $sortingsMethod = [$sortingsProvider, 'sortings'];

        return array_map(function ($sorting) use ($selected) {
                return [
                    'id' => $sorting,
                    'name' => __('sortings.'.$sorting),
                    'selected' => $sorting === $selected,
                ];
        }, call_user_func($sortingsMethod));
    }
}