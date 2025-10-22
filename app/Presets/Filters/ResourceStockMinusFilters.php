<?php
namespace App\Presets\Filters;

class ResourceStockMinusFilters
{
    public static function filters()
    {
        return [
            'in_stock' => [
                'type' => 'presence', 
                'display' => 'list_checkboxes', 
                'multiple' => false, 
                'short_display' => true, 
                'short_selection' => false,
                'locked' => true, // не разрешать изменять
                'values' => function () {
                    return [true];
                },
            ],
            'stock_id' => [
                'type' => 'list', 
                'display' => 'buttons', 
                'multiple' => false, 
                'short_display' => true, 
                'short_selection' => false,
                'locked' => true, // не разрешать изменять
                'values' => function () {
                    return \App\Models\Stock::select('id', 'name')->get();
                },
            ],
            'resource_category_id' => [
                'type' => 'nested_list', 
                'display' => 'list_checkboxes', 
                'multiple' => false, 
                'short_display' => true, 
                'short_selection' => true,
                'values' => function () {
                    return \App\Models\ResourceCategory::rootsWithChildren();
                },
            ]
        ];
    }
}