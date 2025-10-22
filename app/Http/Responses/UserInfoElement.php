<?php
namespace App\Http\Responses;

/**
* @OA\Schema(
*     schema="UserInfoElement",
*     title="Модель информации о пользователе",
*     @OA\Property(property="id", type="integer"),
*     @OA\Property(property="name", type="string"),
*     @OA\Property(property="photo", ref="#/components/schemas/PhotoElement"),
* )
*/
class UserInfoElement extends Response
{
    public function toArray()
    {
        return [
            'id' => $this->data['id'],
            'is_active' => $this->data['is_active'] ?? 0,
            'full_name' => $this->data['full_name'],
            'email' => $this->data['email'],
            'organization' => $this->data['organization'],
            'activity_at' => $this->data['activity_at'],
            'locale' => $this->data['locale'],
            'photo' => $this->data['photo'],
            'is_online' => $this->data['is_online'],
        ];
    }
}