<?php
namespace App\Factories;

class FiltersFactory
{
    public static function create($filtersProvider, $selectedFilters)
    {
        if (!isset($filtersProvider) || !method_exists($filtersProvider, 'filters')) {
            return [];
        }
        
        $filtersMethod = [$filtersProvider, 'filters'];

        return array_map(function($filterId, $filterData) use ($selectedFilters) {
            $nameShortKey = 'filters.'.$filterId.'_short';
            $nameShort = __($nameShortKey);
            $name = __('filters.'.$filterId);

            return [
                'id' => $filterId,               
                'name' => $name,
                'name_short' => $nameShort !== $nameShortKey ? $nameShort : $name,
                'type' => $filterData['type'],
                'display' => $filterData['display'],
                'multiple' => $filterData['multiple'],
                'short_display' => $filterData['short_display'],
                'short_selection' => $filterData['short_selection'],
                'values' => $filterData['values'](),
                'selected_values' => $selectedFilters[$filterId] ?? null
            ];
        }, array_keys(call_user_func($filtersMethod)), call_user_func($filtersMethod));
    }
}