<?php
namespace App\Http\DTO;

/**
 * @OA\Schema(
 *  schema="FilterElement",
 *  type="object",
 *  @OA\Property(property="id", type="string", example="example_filter_id", description="Передаваемый в запрос идентификатор фильтра"),
 *  @OA\Property(property="name", type="string", example="filters_example_filter_id", description="Название фильтра"),
 *  @OA\Property(property="name_short", type="string", example="filters_example_filter_id_short", description="Краткое название фильтра над списком результатов"),
 *  @OA\Property(
 *      property="type", 
 *      type="string",
 *      example="list",
 *      description="Возможные варианты: 'presence' (фильтр по наличию свойства), 'list' (фильтр связей с другими сущностями), 'nested_list' (выбор значений из списка, где каждый родитель содержит вложенный массив children)",
 *      enum={"presence","list","nested_list"},
 *     ),
 *  @OA\Property(
 *      property="display", 
 *      type="string", 
 *      example="buttons",
 *      description="Возможные варианты: 'buttons' (кнопки), 'list_checkboxes' (чекбоксы)",
 *      enum={"buttons","list_checkboxes"},
 *  ),
 *  @OA\Property(property="short_display", type="boolean", description="Отображать по-умолчанию над списком результатов"),
 *  @OA\Property(property="short_selection", type="boolean", example="true", description="Возможность выбора значений во всплывающем окошке"),
 *  @OA\Property(property="multiple", type="boolean", example="true", description="Возможность выбора множества значений"),
 *  @OA\Property(property="values", type="array", description="Список значений фильтра", @OA\Items(type="object")),
 *  @OA\Property(property="selected_values", type="array", description="Список выбранных значений фильтра", @OA\Items(type="object"))
 * )
 */
class FilterDTO {
    private $id;
    private $name;
    private $name_short;
    private $type;
    private $display;
    private $short_display;
    private $short_selection;
    private $multiple;
    private $values;
    private $selected_values;

    public function __construct($params = []) {
        foreach ($params as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function toArray() {
        return get_object_vars($this);
    }
}