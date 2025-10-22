<?php
namespace App\Presets\Filters;

class ResourceStockInventoryFilters
{
    public static function filters()
    {
        return [
            'in_stock' => [
                'type' => 'presence', 
                'display' => 'list_checkboxes', 
                'multiple' => false, 
                'short_display' => true, 
                'short_selection' => true,
                'values' => function () {
                    return [true];
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