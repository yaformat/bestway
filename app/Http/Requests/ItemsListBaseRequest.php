<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

 /** 
 * @OA\Schema(
 *  schema="ItemsListBaseRequest",
 *  title="Базовый запрос для списка элементов раздела",
 *  type="object",
 *  @OA\Property(
 *      property="search",
 *      description="Поисковый запрос",
 *      type="string",
 *      example="",
 *      nullable="true",
 *  ),
 *  @OA\Property(
 *      property="sorting",
 *      description="Примененная сортировка в разделе",
 *      type="string",
 *      example="created_at",
 *      nullable="true",
 *  ),
 *  @OA\Property(
 *      property="page",
 *      description="Страница пагинации",
 *      type="integer",
 *      example=1,
 *      nullable="true"
 *  ),
 *  @OA\Property(
 *      property="limit",
 *      description="Количество элементов на страницу запроса",
 *      type="integer",
 *      example=50,
 *      nullable="true"
 *  ),
 *  @OA\Property(
 *      property="filters",
 *      description="Примененные фильтры в разделе",
 *      type="array", 
 *      nullable="true",
 *      @OA\Items(ref="#/components/schemas/FilterRequestElement")
 *  )
 * ),
 * @OA\Schema(
 *  schema="FilterRequestElement",
 *  type="object",
 *  @OA\Property(property="id", type="string", example="example_filter_id", description="Передаваемый в запрос идентификатор фильтра"),
 *  @OA\Property(property="values", type="array", description="Список выбранных значений фильтра", @OA\Items(type="integer", example="1"))
 * )
 */
class ItemsListBaseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'search' => 'nullable|string',
            'sorting' => 'nullable|string',
            'page' => 'nullable|integer|min:0',
            'limit' => ['nullable', 'integer', function ($attribute, $value, $fail) {
                if ($value != -1 && $value < 1) {
                    $fail($attribute.' должно быть -1 или больше 1.');
                }
            }],
            'only_trashed' => 'nullable|boolean',
            'filters' => 'nullable|array',
            'filters.*' => 'nullable|array',
            //TODO: добавить валидацию для фильтров
        ];
    }

    public function prepareParameters()
    {
        return [
            'request' => $this,
            'search' => $this->getSearch(),
            'filters' => $this-> getFilters(),
            'sortBy' => $this->getSortBy(),
            'limit' => $this->getLimit(),
            'page' => $this->getPage(),
        ];
    }

    public function getSearch(): ?string
    {
        return $this->get('search');
    }

    public function getFilters(): array
    {
        $preset_filters = (isset($this->preset_filters) && is_array($this->preset_filters)) ? $this->preset_filters : [];
        $request_filters = (isset($this->filters) && is_array($this->filters)) ? $this->filters : [];

        //print_r($preset_filters); exit;
        //return $request_filters;
        $all_filters =  array_merge($preset_filters, $request_filters);
        
        if (empty($all_filters)) return [];
        //print_r($all_filters); exit;
        return $all_filters;

        $filters = [];
        foreach ($all_filters as $filter) {
            if (!isset($filters[$filter['id']])) {
                $filters[$filter['id']][] = $filter;
            }
        }

        return $filters;
    }

    public function getSortBy(): string
    {
        $sorting = $this->get('sorting'); 
        return ($sorting) ? $sorting : 'created_at';
    }

    public function getPage(): int
    {
        $page = $this->get('page', 1);
        if (intval($page) <= 0) $page = 1;

        return $page;
    }

    public function getLimit(): ?int
    {
        $limit = $this->get('limit', 20);
        if (intval($limit) === -1) return null; // Возвращаем null для получения всех записей
        if (intval($limit) <= 0) $limit = 20;

        return $limit;
    }
}