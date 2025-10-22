<?php
namespace App\Http\Responses;

use App\Factories\SortingsFactory;
use App\Factories\FiltersFactory;

use App\Http\DTO\SortingDTO;
use App\Http\DTO\FilterDTO;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use App\Utils\ItemsListSearchParameters;

/** 
 * @OA\Schema(
 *     schema="ItemsListBaseResponse",
 *     title="Базовый ответ для списка элементов раздела",
 *     type="object",
 *          @OA\Property(
 *               property="sortings",
 *               type="array",
 *               description="Массив доступных сортировок",
 *               @OA\Items(ref="#/components/schemas/SortingElement")
 *          ),
 *          @OA\Property(
 *               property="filters",
 *               type="array",
 *               description="Массив доступных фильтров",
 *               @OA\Items(ref="#/components/schemas/FilterElement")
 *          ),
 *          @OA\Property(
 *               property="has_search",
 *               type="boolean",
 *               description="Поддерживается ли поиск"
 *          ),
 *          @OA\Property(
 *               property="search",
 *               type="string",
 *               nullable="true",
 *               description="Текущий поисковый запрос"
 *          ),
 *          @OA\Property(
 *               property="count",
 *               type="integer",
 *               description="Текущее количество возвращённых элементов"
 *          ),
 *          @OA\Property(
 *               property="page",
 *               type="integer",
 *               description="Текущая страница пагинации"
 *          ),
 *          @OA\Property(
 *               property="total_count",
 *               type="integer",
 *               description="Общее количество элементов без учёта пагинации"
 *          ),
 *          @OA\Property(
 *               property="total_page",
 *               type="integer",
 *               description="Общее количество страниц пагинации, зависит от значения 'count' в запросе"
 *          ),
 *          @OA\Property(
 *              property="items",
 *              type="array",
 *              @OA\Items(type="object"),
 *              description="Массив элементов данных"
 *          )
 * )
 */
class ItemsListBaseResponse
{
    protected $filtersPresetClass;
    protected $sortingsPresetClass;
    public array $sortings;
    public array $filters;
    public $has_search;
    public $search;
    public int $count;
    public int $page;
    public int $total_count;
    public int $trashed_count;
    public int $total_page;
    public int $limit;
    public array $items;

    public function __construct($filtersPresetClass, $sortingsPresetClass, $items, ItemsListSearchParameters $parameters, $pagination)
    {
        $this->filtersPresetClass = $filtersPresetClass;
        $this->sortingsPresetClass = $sortingsPresetClass;
        $this->has_search = $this->hasSearch();
        $this->items = is_object($items) ? $items->toArray() : (is_array($items) ? $items : []);
        $this->sortings = $this->createSortings($parameters->sortBy);
        $this->filters = $this->createFilters($parameters->filters);
        $this->search = $parameters->search;

        if ($pagination instanceof LengthAwarePaginator || $pagination instanceof Paginator) {
            $this->count = $pagination->count();
            $this->total_count = $pagination->total();
            $this->page = $pagination->currentPage();
            $this->total_page = $pagination->lastPage();
        } else {
            $this->count = count($items);
            $this->total_count = count($items);
            $this->page = 1;
            $this->total_page = 1;
        }

        $this->trashed_count = 0;
        $this->limit = $parameters->limit ?? 0;
    }

    private function hasSearch(): bool
    {
        $hasSearch = true;

        $filtersProvider = new $this->filtersPresetClass;
        if (isset($filtersProvider) && method_exists($filtersProvider, 'hasSearch')) {
            $hasSearch = $filtersProvider->hasSearch();
        }

        return $hasSearch;
    }

    private function createSortings(string $selectedSorting): array 
    {
        return $this->createDTOs(
            SortingsFactory::create($this->sortingsPresetClass, $selectedSorting),
            SortingDTO::class
        );
    }
    
    private function createFilters(array $selectedFilters): array
    {
        return $this->createDTOs(
            FiltersFactory::create($this->filtersPresetClass, $selectedFilters),
            FilterDTO::class
        );
    }

    private function createDTOs(array $items, string $dtoClass): array
    {
        return Collection::make($items)
            ->map(fn($item) => (new $dtoClass($item))->toArray())
            ->all();
    }

    // Метод для установки кастомных фильтров
    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    // Метод для установки кастомных сортировок
    public function setSortings(array $sortings): void
    {
        $this->sortings = $sortings;
    }

    public function toArray(): array
    {
        $vars = get_object_vars($this);
        unset($vars['filtersPresetClass']);
        unset($vars['sortingsPresetClass']);
        return $vars;
    }
}
