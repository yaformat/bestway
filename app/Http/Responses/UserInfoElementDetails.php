<?php
namespace App\Http\Responses;

/**
* @OA\Schema(
*     schema="UserInfoElementDetails",
*     title="Детальная модель информациии о пользователе",
*       allOf={
*           @OA\Schema(ref="#/components/schemas/UserInfoElement"),
*       }
* )
*/
class UserInfoElementDetails extends UserInfoElement
{
    public function toArray()
    {
        $parentData = parent::toArray();
        $detailsData = [
            'positions' => $this->data['positions'],
            'all_permissions' => $this->data['all_permissions'],
        ];

        return array_merge($parentData, $detailsData);
    }
}