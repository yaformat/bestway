<?php

namespace App\Helpers;

use App\Models\ResourceCategory;
use App\Models\Stock;
use App\Models\Kitchen;
use App\Models\Workshop;
use App\Models\Direction;
use App\Models\Resort;
use App\Models\RestType;
use App\Enums\ResourceTypeEnum;
use App\Helpers\FilterConfig;
use App\Helpers\CacheHelper;

class FilterHelper
{
    // Время жизни кеша в секундах (по умолчанию 1 час)
    private const CACHE_TTL = 3600;
    // Префикс для кеша
    private const CACHE_PREFIX = 'filter_helper';

    /**
     * Создает конфигурацию фильтра
     */
    public static function createFilter(string $label, string $type): FilterConfig
    {
        return new FilterConfig($label, $type);
    }

    /**
     * Получает опции для направлений в древовидном формате
     */
    public static function getDirectionOptions(): array
    {
        return CacheHelper::rememberByKey(
            self::CACHE_PREFIX,
            'direction_options_tree',
            function () {
                // Получаем корневые направления (без родителя)
                $rootDirections = Direction::whereNull('parent_id')
                    ->orWhere('parent_id', 0)
                    ->active()
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get();
                
                return $rootDirections->map(function ($direction) {
                    return self::buildDirectionNode($direction);
                })->toArray();
            },
            [],
            self::CACHE_TTL
        );
    }

    /**
     * Строит узел дерева направлений
     */
    private static function buildDirectionNode(Direction $direction): array
    {
        $node = [
            'value' => $direction->id,
            'name' => $direction->name,
        ];
        
        // Получаем дочерние направления
        $children = $direction->children()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        
        if ($children->isNotEmpty()) {
            $node['children'] = $children->map(function ($child) {
                return self::buildDirectionNode($child);
            })->toArray();
        }
        
        return $node;
    }

    /**
     * Получает плоский список направлений (для селектов без иерархии)
     */
    public static function getDirectionOptionsFlat(): array
    {
        return CacheHelper::rememberByKey(
            self::CACHE_PREFIX,
            'direction_options_flat',
            function () {
                return Direction::active()
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get()
                    ->map(function ($direction) {
                        return [
                            'value' => $direction->id,
                            'name' => $direction->name
                        ];
                    })
                    ->toArray();
            },
            [],
            self::CACHE_TTL
        );
    }

    /**
     * Получает опции для курортов с учетом направления
     */
    public static function getResortOptions(?int $directionId = null): array
    {
        return CacheHelper::rememberByKey(
            self::CACHE_PREFIX,
            'resort_options',
            function () use ($directionId) {
                $query = Resort::active()->with('direction');
                
                if ($directionId) {
                    $query->where('direction_id', $directionId);
                }
                
                return $query->orderBy('name')
                    ->get()
                    ->map(function ($resort) {
                        return [
                            'value' => $resort->id,
                            'name' => $resort->name . ' (' . ($resort->direction->name ?? 'Без направления') . ')'
                        ];
                    })
                    ->toArray();
            },
            [$directionId ?? 'all'],
            self::CACHE_TTL
        );
    }

    /**
     * Получает опции для видов отдыха
     */
    public static function getRestTypeOptions(): array
    {
        return CacheHelper::rememberByKey(
            self::CACHE_PREFIX,
            'rest_type_options',
            function () {
                return RestType::active()
                    ->orderBy('name')
                    ->get()
                    ->map(function ($restType) {
                        return [
                            'value' => $restType->id,
                            'name' => $restType->name
                        ];
                    })
                    ->toArray();
            },
            [],
            self::CACHE_TTL
        );
    }

    /**
     * Получает опции для удобств отеля
     */
    public static function getHotelFacilities(): array
    {
        return [
            ['value' => 'wifi', 'name' => 'Wi-Fi'],
            ['value' => 'parking', 'name' => 'Парковка'],
            ['value' => 'pool', 'name' => 'Бассейн'],
            ['value' => 'spa', 'name' => 'SPA'],
            ['value' => 'gym', 'name' => 'Фитнес-центр'],
            ['value' => 'restaurant', 'name' => 'Ресторан'],
            ['value' => 'bar', 'name' => 'Бар'],
            ['value' => 'room_service', 'name' => 'Обслуживание номеров'],
            ['value' => 'concierge', 'name' => 'Консьерж'],
            ['value' => 'pet_friendly', 'name' => 'Разрешены животные'],
            ['value' => 'business_center', 'name' => 'Бизнес-центр'],
            ['value' => 'meeting_rooms', 'name' => 'Переговорные комнаты'],
            ['value' => 'laundry', 'name' => 'Прачечная'],
            ['value' => 'shuttle', 'name' => 'Трансфер'],
            ['value' => 'beach_access', 'name' => 'Доступ к пляжу'],
            ['value' => 'ski_storage', 'name' => 'Хранение лыж'],
            ['value' => 'elevator', 'name' => 'Лифт'],
            ['value' => 'ac', 'name' => 'Кондиционер'],
            ['value' => 'minibar', 'name' => 'Минибар'],
            ['value' => 'safe', 'name' => 'Сейф'],
            ['value' => 'conference', 'name' => 'Конференц-зал'],
            ['value' => 'fitness', 'name' => 'Фитнес'],
        ];
    }

    /**
     * Получает опции для звездности отелей
     */
    public static function getHotelStarsOptions(): array
    {
        return [
            ['value' => '5', 'name' => '5 звезд'],
            ['value' => '4', 'name' => '4 звезды'],
            ['value' => '3', 'name' => '3 звезды'],
            ['value' => '2', 'name' => '2 звезды'],
            ['value' => '1', 'name' => '1 звезда'],
        ];
    }

    /**
     * Получает опции для валют
     */
    public static function getCurrencyOptions(): array
    {
        return [
            ['value' => 'USD', 'name' => 'USD ($)'],
            ['value' => 'EUR', 'name' => 'EUR (€)'],
            ['value' => 'RUB', 'name' => 'RUB (₽)'],
            ['value' => 'KGS', 'name' => 'KGS (с)'],
            ['value' => 'KZT', 'name' => 'KZT (₸)'],
            ['value' => 'UZS', 'name' => 'UZS (сўм)'],
        ];
    }

    /**
     * Собирает категории в древовидном формате для фильтра
     */
    public static function getCategoryTree($type = null): array
    {
        return CacheHelper::rememberByKey(
            self::CACHE_PREFIX,
            'category_tree',
            function () use ($type) {
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
            },
            [$type ?? 'all'],
            self::CACHE_TTL
        );
    }

    /**
     * Строит узел дерева категорий
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
     */
    public static function getCategoryTreeByType($type): array
    {
        return self::getCategoryTree($type);
    }

    /**
     * Получает все категории в виде дерева (без фильтрации по типу)
     */
    public static function getAllCategoriesTree(): array
    {
        return self::getCategoryTree();
    }

    /**
     * Получает список типов ресурсов для фильтра
     */
    public static function getResourceTypes(): array
    {
        return CacheHelper::rememberByKey(
            self::CACHE_PREFIX,
            'resource_types',
            function () {
                return collect(ResourceTypeEnum::cases())->map(function ($type) {
                    return [
                        'value' => $type->value,
                        'name' => $type->getLabel(),
                    ];
                })->toArray();
            },
            [],
            self::CACHE_TTL
        );
    }

    /**
     * Фильтрует список типов ресурсов по заданным значениям
     *
     * @param array $availableTypes Массив доступных типов ресурсов
     * @return array Отфильтрованный список типов ресурсов
     */
    public static function filterResourceTypes(array $availableTypes): array
    {
        $allTypes = self::getResourceTypes();
        // Преобразуем объекты ResourceTypeEnum в строки
        $availableTypeValues = array_map(function ($type) {
            if ($type instanceof ResourceTypeEnum) {
                return $type->value;
            }
            return $type;
        }, $availableTypes);
        return collect($allTypes)
            ->filter(function ($type) use ($availableTypeValues) {
                return in_array($type['value'], $availableTypeValues);
            })
            ->values()
            ->toArray();
    }

    /**
     * Фильтрует дерево категорий по заданным ID категорий
     *
     * @param array $availableCategoryIds Массив доступных ID категорий
     * @param mixed $type Тип ресурса (опционально)
     * @return array Отфильтрованное дерево категорий
     */
    public static function filterCategoryTree(array $availableCategoryIds, $type = null): array
    {
        $fullTree = self::getCategoryTree($type);
        // Если массив ID категорий пуст, возвращаем пустой массив
        if (empty($availableCategoryIds)) {
            return [];
        }
        // Функция для фильтрации узлов дерева
        $filterNode = function ($node) use (&$filterNode, $availableCategoryIds) {
            // Проверяем, есть ли текущий узел в списке доступных ID
            $nodeIncluded = in_array($node['value'], $availableCategoryIds);
            // Если у узла есть дочерние элементы, фильтруем их
            if (isset($node['children']) && !empty($node['children'])) {
                $filteredChildren = [];
                foreach ($node['children'] as $child) {
                    $filteredChild = $filterNode($child);
                    if ($filteredChild !== null) {
                        $filteredChildren[] = $filteredChild;
                        $nodeIncluded = true; // Если хотя бы один дочерний элемент включен, включаем и родителя
                    }
                }
                if (!empty($filteredChildren)) {
                    $node['children'] = $filteredChildren;
                } else {
                    unset($node['children']);
                }
            }
            // Возвращаем узел, если он включен или имеет включенных потомков
            return $nodeIncluded ? $node : null;
        };
        // Фильтруем корневые узлы
        $filteredTree = [];
        foreach ($fullTree as $rootNode) {
            $filteredNode = $filterNode($rootNode);
            if ($filteredNode !== null) {
                $filteredTree[] = $filteredNode;
            }
        }
        return $filteredTree;
    }

    /**
     * Фильтрует дерево направлений по заданным ID
     *
     * @param array $availableDirectionIds Массив доступных ID направлений
     * @return array Отфильтрованное дерево направлений
     */
    public static function filterDirectionTree(array $availableDirectionIds): array
    {
        $fullTree = self::getDirectionOptions();
        // Если массив ID направлений пуст, возвращаем пустой массив
        if (empty($availableDirectionIds)) {
            return [];
        }
        // Функция для фильтрации узлов дерева
        $filterNode = function ($node) use (&$filterNode, $availableDirectionIds) {
            // Проверяем, есть ли текущий узел в списке доступных ID
            $nodeIncluded = in_array($node['value'], $availableDirectionIds);
            // Если у узла есть дочерние элементы, фильтруем их
            if (isset($node['children']) && !empty($node['children'])) {
                $filteredChildren = [];
                foreach ($node['children'] as $child) {
                    $filteredChild = $filterNode($child);
                    if ($filteredChild !== null) {
                        $filteredChildren[] = $filteredChild;
                        $nodeIncluded = true; // Если хотя бы один дочерний элемент включен, включаем и родителя
                    }
                }
                if (!empty($filteredChildren)) {
                    $node['children'] = $filteredChildren;
                } else {
                    unset($node['children']);
                }
            }
            // Возвращаем узел, если он включен или имеет включенных потомков
            return $nodeIncluded ? $node : null;
        };
        // Фильтруем корневые узлы
        $filteredTree = [];
        foreach ($fullTree as $rootNode) {
            $filteredNode = $filterNode($rootNode);
            if ($filteredNode !== null) {
                $filteredTree[] = $filteredNode;
            }
        }
        return $filteredTree;
    }


    /**
     * Извлекает значения из плоского массива опций
     */
    public static function extractValues(array $options): array
    {
        return collect($options)->pluck('value')->toArray();
    }

    /**
     * Находит узел в дереве по значению
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

    /**
     * Получает список складов для фильтра
     */
    public static function getStockOptions(): array
    {
        return CacheHelper::rememberByKey(
            self::CACHE_PREFIX,
            'stock_options',
            function () {
                return Stock::orderBy('name')
                    ->get()
                    ->map(function ($stock) {
                        return [
                            'value' => $stock->id,
                            'name' => $stock->name,
                        ];
                    })->toArray();
            },
            [],
            self::CACHE_TTL
        );
    }

    /**
     * Получает список кухонь для фильтра
     */
    public static function getKitchenOptions(): array
    {
        return CacheHelper::rememberByKey(
            self::CACHE_PREFIX,
            'kitchen_options',
            function () {
                return Kitchen::orderBy('name')
                    ->get()
                    ->map(function ($kitchen) {
                        return [
                            'value' => $kitchen->id,
                            'name' => $kitchen->name,
                        ];
                    })->toArray();
            },
            [],
            self::CACHE_TTL
        );
    }

    /**
     * Получает список мастерских для фильтра
     */
    public static function getWorkshopOptions(): array
    {
        return CacheHelper::rememberByKey(
            self::CACHE_PREFIX,
            'workshop_options',
            function () {
                return Workshop::orderBy('name')
                    ->get()
                    ->map(function ($workshop) {
                        return [
                            'value' => $workshop->id,
                            'name' => $workshop->name,
                        ];
                    })->toArray();
            },
            [],
            self::CACHE_TTL
        );
    }

    /**
     * Получает список опций для простого select фильтра
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
     */
    public static function getModelOptions(string $modelClass, string $valueField = 'id', string $nameField = 'name', string $orderBy = 'name', ?int $cacheTtl = null): array
    {
        if ($cacheTtl === null) {
            // Без кеширования
            return $modelClass::orderBy($orderBy)
                ->get()
                ->map(function ($item) use ($valueField, $nameField) {
                    return [
                        'value' => $item->{$valueField},
                        'name' => $item->{$nameField},
                    ];
                })->toArray();
        }
        return CacheHelper::rememberByKey(
            self::CACHE_PREFIX,
            'model_options',
            function () use ($modelClass, $valueField, $nameField, $orderBy) {
                return $modelClass::orderBy($orderBy)
                    ->get()
                    ->map(function ($item) use ($valueField, $nameField) {
                        return [
                            'value' => $item->{$valueField},
                            'name' => $item->{$nameField},
                        ];
                    })->toArray();
            },
            [$modelClass, $valueField, $nameField, $orderBy],
            $cacheTtl
        );
    }

    /**
     * Извлекает все значения из древовидной структуры категорий
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
     * Очищает кеш фильтров
     */
    public static function clearCache(?string $pattern = null): void
    {
        if ($pattern === null) {
            CacheHelper::clearByPrefix(self::CACHE_PREFIX);
        } else {
            CacheHelper::forgetByPattern(self::CACHE_PREFIX . '_' . $pattern . '*');
        }
    }

    /**
     * Очищает кеш для конкретного метода
     */
    public static function clearMethodCache(string $method, array $params = []): void
    {
        CacheHelper::forgetMethod(self::CACHE_PREFIX, $method, $params);
    }

    /**
     * Получает все активные ключи кеша FilterHelper
     */
    public static function getCacheKeys(): array
    {
        return CacheHelper::getKeysByPattern(self::CACHE_PREFIX . '_*');
    }

    /**
     * Получает отфильтрованные опции направлений для формы
     */
    public static function getDirectionOptionsForForm(?int $parentId = null): array
    {
        return CacheHelper::rememberByKey(
            self::CACHE_PREFIX,
            'direction_options_form',
            function () use ($parentId) {
                $query = Direction::active();
                
                if ($parentId !== null) {
                    $query->where('parent_id', $parentId);
                } else {
                    $query->where(function($q) {
                        $q->whereNull('parent_id')
                          ->orWhere('parent_id', 0);
                    });
                }
                
                return $query->orderBy('sort_order')
                    ->orderBy('name')
                    ->get()
                    ->map(function ($direction) {
                        return [
                            'value' => $direction->id,
                            'name' => $direction->name,
                            'has_children' => $direction->children()->active()->exists()
                        ];
                    })
                    ->toArray();
            },
            [$parentId ?? 'root'],
            self::CACHE_TTL
        );
    }

    /**
     * Получает опции курортов с группировкой по направлениям
     */
    public static function getResortOptionsGrouped(): array
    {
        return CacheHelper::rememberByKey(
            self::CACHE_PREFIX,
            'resort_options_grouped',
            function () {
                $resorts = Resort::active()
                    ->with('direction')
                    ->orderBy('name')
                    ->get()
                    ->groupBy('direction_id');
                
                $groupedOptions = [];
                foreach ($resorts as $directionId => $directionResorts) {
                    $directionName = $directionResorts->first()->direction->name ?? 'Без направления';
                    
                    $groupedOptions[] = [
                        'label' => $directionName,
                        'options' => $directionResorts->map(function ($resort) {
                            return [
                                'value' => $resort->id,
                                'name' => $resort->name
                            ];
                        })->toArray()
                    ];
                }
                
                return $groupedOptions;
            },
            [],
            self::CACHE_TTL
        );
    }

    /**
     * Получает опции для фильтра с учетом дочерних элементов
     */
    public static function getDirectionOptionsWithChildren(): array
    {
        $directions = self::getDirectionOptions();
        
        // Добавляем информацию о количестве дочерних элементов
        return array_map(function ($direction) {
            if (isset($direction['children'])) {
                $direction['has_children'] = true;
                $direction['children_count'] = count($direction['children']);
                $direction['children'] = self::getDirectionOptionsWithChildrenRecursive($direction['children']);
            } else {
                $direction['has_children'] = false;
                $direction['children_count'] = 0;
            }
            return $direction;
        }, $directions);
    }

    /**
     * Рекурсивно обрабатывает дочерние направления
     */
    private static function getDirectionOptionsWithChildrenRecursive(array $children): array
    {
        return array_map(function ($child) {
            if (isset($child['children'])) {
                $child['has_children'] = true;
                $child['children_count'] = count($child['children']);
                $child['children'] = self::getDirectionOptionsWithChildrenRecursive($child['children']);
            } else {
                $child['has_children'] = false;
                $child['children_count'] = 0;
            }
            return $child;
        }, $children);
    }

    /**
     * Получает плоский список направлений с указанием уровня вложенности
     */
    public static function getDirectionOptionsFlatWithLevel(): array
    {
        $tree = self::getDirectionOptions();
        $flatList = [];
        
        self::flattenDirectionTree($tree, $flatList, 0);
        
        return $flatList;
    }

    /**
     * Рекурсивно преобразует дерево в плоский список
     */
    private static function flattenDirectionTree(array $tree, array &$flatList, int $level): void
    {
        foreach ($tree as $node) {
            $flatList[] = [
                'value' => $node['value'],
                'name' => str_repeat('— ', $level) . $node['name'],
                'level' => $level
            ];
            
            if (isset($node['children']) && !empty($node['children'])) {
                self::flattenDirectionTree($node['children'], $flatList, $level + 1);
            }
        }
    }
}