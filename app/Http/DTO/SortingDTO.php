<?php
namespace App\Http\DTO;

/**
 * @OA\Schema(
 *  schema="SortingElement",
 *  type="object",
 *  @OA\Property(property="id", type="string", example="created_at", description="Передаваемый в запрос идентификатор сортировки"),
 *  @OA\Property(property="name", type="string", example="По наименованию", description="Название сортировки для отображения"),
 *  @OA\Property(property="selected", type="boolean", example="true", description="Указывает, выбрана или нет данная сортировка"),
 * )
 */
class SortingDTO {
    private $id;
    private $name;
    private $selected;

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