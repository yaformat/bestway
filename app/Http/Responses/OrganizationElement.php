<?php
namespace App\Http\Responses;

/**
* @OA\Schema(
*     schema="OrganizationElement",
*     title="Модель Организация",
*     @OA\Property(property="id", type="integer"),
*     @OA\Property(property="name", type="string"),
*     @OA\Property(property="created_at", type="string", format="date-time"),
*     @OA\Property(property="updated_at", type="string", format="date-time"),
*     @OA\Property(property="deleted_at", type="string", format="date-time")
* )
*/
class OrganizationElement extends Response
{
    public function toArray()
    {
        return [
            'id' => $this->data['id'],
            'is_active' => $this->data['is_active'],
            'name' => $this->data['name'],
            'photo' => $this->data['photo'] ?? null,
            'owner' => $this->data['owner'],
            'users_count' => $this->data['users_count'] ?? 0,
            'created_at' => $this->data['created_at'],
            'updated_at' => $this->data['updated_at'],
            'deleted_at' => $this->data['deleted_at'] ?? null,
        ];
    }
}