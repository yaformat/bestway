<?php
namespace App\Http\Responses;

/**
* @OA\Schema(
*     schema="BookingPeriodElement",
*     title="Элемент периода бронирования",
*     type="object",
*     @OA\Property(property="id", type="integer", description="ID периода"),
*     @OA\Property(property="name", type="string", nullable=true, description="Название"),
*     @OA\Property(property="start_date", type="string", format="date", description="Дата начала"),
*     @OA\Property(property="end_date", type="string", format="date", description="Дата окончания"),
*     @OA\Property(property="is_active", type="boolean", description="Активность"),
*     @OA\Property(property="sort_order", type="integer", description="Порядок сортировки"),
*     @OA\Property(property="created_at", type="string", format="date-time", description="Дата создания"),
*     @OA\Property(property="updated_at", type="string", format="date-time", description="Дата обновления")
* )
*/
class BookingPeriodElement
{
    protected $period;
    
    public function __construct($period)
    {
        $this->period = $period;
    }
    
    public function toArray()
    {
        return [
            'id' => $this->period->id,
            'name' => $this->period->name,
            'start_date' => $this->period->start_date,
            'end_date' => $this->period->end_date,
            'is_active' => $this->period->is_active,
            'sort_order' => $this->period->sort_order,
            'created_at' => $this->period->created_at,
            'updated_at' => $this->period->updated_at
        ];
    }
}