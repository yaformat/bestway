<?php

namespace App\Helpers;

use App\Models\ResourceCategory;
use App\Models\Stock;
use App\Models\Kitchen;
use App\Models\Workshop;
use App\Enums\ResourceTypeEnum;
use App\Helpers\FilterConfig;


class FilterHelperNoCache
{
    /**
     * Создает конфигурацию фильтра
     *
     * @param string $label
     * @param string $type
     * @return FilterConfig
     */
    public static function createFilter(string $label, string $type): FilterConfig
    {
        return new FilterConfig($label, $type);
    }

    /**
     * Собирает категории в древовидном формате для фильтра
     *
     * @param ResourceTypeEnum|string|null $type Тип категорий для фильтрации
     * @return array
     */
    public static function getCategoryTree($type = null): array
    {
        // Преобразуем строку в enum, если необходимо
        if (is_string($type)) {
            $type = ResourceTypeEnum::tryFrom($type);
        }
        
        $query = ResourceCategory::whereNull('parent_id')
            ->orWhere('parent_id', 0);
        
        if ($type) {
            $query->where('type', $type);
        }
        
        $rootCategories = $query->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        
        return $rootCategories->map(function ($category) use ($type) {
            return self::buildCategoryNode($category, $type);
        })->toArray();
    }

    /**
     * Строит узел дерева категорий
     *
     * @param ResourceCategory $category
     * @param ResourceTypeEnum|null $type
     * @return array
     */
    private static function buildCategoryNode(ResourceCategory $category, ?ResourceTypeEnum $type = null): array
    {
        $node = [
            'value' => $category->id,
            'name' => $category->name,
        ];

        $childrenQuery = $category->children()->orderBy('sort_order')->orderBy('name');
        
        if ($type) {
            $childrenQuery->where('type', $type);
        }
        
        $children = $childrenQuery->get();
        
        if ($children->isNotEmpty()) {
            $node['children'] = $children->map(function ($child) use ($type) {
                return self::buildCategoryNode($child, $type);
            })->toArray();
        }

        return $node;
    }

    /**
     * Получает дерево категорий для конкретного типа ресурса
     *
     * @param ResourceTypeEnum|string $type
     * @return array
     */
    public static function getCategoryTreeByType($type): array
    {
        return self::getCategoryTree($type);
    }

    /**
     * Получает все категории в виде дерева (без фильтрации по типу)
     *
     * @return array
     */
    public static function getAllCategoriesTree(): array
    {
        return self::getCategoryTree();
    }

    /**
     * Получает список типов ресурсов для фильтра
     *
     * @return array
     */
    public static function getResourceTypes(): array
    {
        return collect(ResourceTypeEnum::cases())->map(function ($type) {
            return [
                'value' => $type->value,
                'name' => $type->getLabel(), // Предполагается, что у enum есть метод getLabel()
            ];
        })->toArray();
    }

    /**
     * Получает список складов для фильтра
     *
     * @return array
     */
    public static function getStockOptions(): array
    {
        return Stock::orderBy('name')
            ->get()
            ->map(function ($stock) {
                return [
                    'value' => $stock->id,
                    'name' => $stock->name,
                ];
            })->toArray();
    }

    /**
     * Получает список кухонь для фильтра
     *
     * @return array
     */
    public static function getKitchenOptions(): array
    {
        return Kitchen::orderBy('name')
            ->get()
            ->map(function ($kitchen) {
                return [
                    'value' => $kitchen->id,
                    'name' => $kitchen->name,
                ];
            })->toArray();
    }

    /**
     * Получает список мастерских для фильтра
     *
     * @return array
     */
    public static function getWorkshopOptions(): array
    {
        return Workshop::orderBy('name')
            ->get()
            ->map(function ($workshop) {
                return [
                    'value' => $workshop->id,
                    'name' => $workshop->name,
                ];
            })->toArray();
    }

    /**
     * Получает список опций для простого select фильтра
     *
     * @param array $data
     * @param string $valueField
     * @param string $nameField
     * @return array
     */
    public static function getSelectOptions(array $data, string $valueField = 'id', string $nameField = 'name'): array
    {
        return collect($data)->map(function ($item) use ($valueField, $nameField) {
            return [
                'value' => $item[$valueField],
                'name' => $item[$nameField],
            ];
        })->toArray();
    }

    /**
     * Универсальный метод для получения опций из модели
     *
     * @param string $modelClass
     * @param string $valueField
     * @param string $nameField
     * @param string $orderBy
     * @return array
     */
    public static function getModelOptions(string $modelClass, string $valueField = 'id', string $nameField = 'name', string $orderBy = 'name'): array
    {
        return $modelClass::orderBy($orderBy)
            ->get()
            ->map(function ($item) use ($valueField, $nameField) {
                return [
                    'value' => $item->{$valueField},
                    'name' => $item->{$nameField},
                ];
            })->toArray();
    }

    /**
     * Извлекает все значения из древовидной структуры категорий
     *
     * @param array $tree Древовидная структура категорий
     * @return array Массив всех значений (ID категорий)
     */
    public static function extractValuesFromTree(array $tree): array
    {
        $values = [];
        
        foreach ($tree as $node) {
            $values[] = $node['value'];
            
            if (isset($node['children']) && !empty($node['children'])) {
                $childValues = self::extractValuesFromTree($node['children']);
                $values = array_merge($values, $childValues);
            }
        }
        
        return $values;
    }

    /**
     * Извлекает значения из плоского массива опций
     *
     * @param array $options Массив опций с ключами 'value' и 'name'
     * @return array Массив значений
     */
    public static function extractValues(array $options): array
    {
        return collect($options)->pluck('value')->toArray();
    }

    /**
     * Находит узел в дереве по значению
     *
     * @param array $tree Древовидная структура
     * @param mixed $value Искомое значение
     * @return array|null Найденный узел или null
     */
    public static function findNodeByValue(array $tree, $value): ?array
    {
        foreach ($tree as $node) {
            if ($node['value'] == $value) {
                return $node;
            }
            
            if (isset($node['children']) && !empty($node['children'])) {
                $found = self::findNodeByValue($node['children'], $value);
                if ($found !== null) {
                    return $found;
                }
            }
        }
        
        return null;
    }
}
