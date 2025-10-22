<?php
namespace App\Presets\Filters;

class ResourceFilters
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
            'min_stock' => [
                'type' => 'presence', 
                'display' => 'list_checkboxes', 
                'multiple' => false, 
                'short_display' => true, 
                'short_selection' => true,
                'values' => function () {
                    return [true];
                },
            ],
            'deficiency_min_stock' => [
                'type' => 'presence', 
                'display' => 'list_checkboxes', 
                'multiple' => false, 
                'short_display' => false, 
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
            ],
            'stock_id' => [
                'type' => 'list', 
                'display' => 'buttons', 
                'multiple' => true, 
                'short_display' => true, 
                'short_selection' => true,
                'values' => function () {
                    return \App\Models\Stock::select('id', 'name')->get();
                    //return auth()->user()->stockPermissions()->select('stocks.id', 'stocks.name')->get(); //только те склады, на которые есть права
                },
            ]
        ];
    }
}