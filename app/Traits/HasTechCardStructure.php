<?php

namespace App\Traits;

use App\Models\TechCardGroup;
use App\Models\TechCardStep;
use App\Models\Workshop;
use App\Models\Kitchen;
use App\Models\ResourceTypes\EquipmentResource;
use App\Models\TechCardHasEquipment;
use App\Models\TechCardHasKitchen;
use App\Services\OptimizedAvailabilityService;
use App\Services\OptimizedReservationService;
use App\Helpers\Helper;

trait HasTechCardStructure
{
    protected static function bootHasTechCardStructure()
    {
        static::deleting(function ($model) {
            // При удалении ресурса удаляем все связанные данные
            $model->groups()->delete();
            $model->steps()->delete();
            $model->has_equipments()->delete();
            $model->has_kitchens()->delete(); // Удаляем связи с кухнями
        });

        static::retrieved(function ($model) {
            $model->appends = array_merge(
                $model->appends,
                ['cooking_time', 'ready_time', 'cost_price']
            );
        });
    }

    public function initializeHasTechCardStructure()
    {
        // Добавляем поля к fillable через initialize метод
        $this->fillable = array_merge($this->fillable, [
            'cooking_time_hours',
            'cooking_time_minutes',
            'ready_time_hours',
            'ready_time_minutes',
            'workshop_id',
        ]);
    }

    public function groups()
    {
        return $this->hasMany(TechCardGroup::class, 'tech_card_resource_id');
    }

    public function steps()
    {
        return $this->hasMany(TechCardStep::class, 'tech_card_resource_id')->orderBy('sort_order');
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class, 'workshop_id')->withTrashed();
    }

    public function equipment()
    {
        return $this->hasManyThrough(
            EquipmentResource::class, 
            TechCardHasEquipment::class, 
            'tech_card_resource_id',
            'id', 
            'id', 
            'resource_id'
        )
        ->withTrashed()
        ->select(['resources.id', 'resources.name']);
    }

    public function kitchens()
    {
        return $this->hasManyThrough(
            Kitchen::class, 
            TechCardHasKitchen::class, 
            'tech_card_resource_id', 
            'id', 
            'id', 
            'kitchen_id'
        )
        ->withTrashed()
        ->select(['kitchens.id', 'kitchens.name']);
    }

    // Прямые связи с промежуточными таблицами для управления
    public function has_equipments()
    {
        return $this->hasMany(TechCardHasEquipment::class, 'tech_card_resource_id');
    }

    public function has_kitchens()
    {
        return $this->hasMany(TechCardHasKitchen::class, 'tech_card_resource_id');
    }

    public function getCookingTimeAttribute()
    {
        if (empty($this->cooking_time_hours) && empty($this->cooking_time_minutes)) {
            return null;
        }
        return Helper::formatTimeObject($this->cooking_time_hours, $this->cooking_time_minutes);
    }

    public function getReadyTimeAttribute()
    {
        if (empty($this->ready_time_hours) && empty($this->ready_time_minutes)) {
            return null;
        }
        return Helper::formatTimeObject($this->ready_time_hours, $this->ready_time_minutes);
    }

    public function getCostPriceAttribute()
    {
        $totalCost = 0;
        foreach ($this->groups as $group) {
            foreach ($group->resources as $resource) {
                $totalCost += $resource->total_price ?? 0;
            }
        }
        return Helper::formatPriceObject($totalCost);
    }
    
    /**
     * Быстрая проверка доступности ресурса
     */
    public function isAvailable(int $quantity = 1): bool
    {
        return app(OptimizedAvailabilityService::class)->quickAvailabilityCheck($this->id, $quantity);
    }

    /**
     * Проверить доступность ресурса с детальной информацией
     */
    public function checkAvailability(int $quantity = 1): array
    {
        return app(OptimizedAvailabilityService::class)->checkTechCardAvailability($this->id, $quantity);
    }

    /**
     * Проверить доступность ресурса в конкретном цехе
     * @deprecated используйте checkAvailability(), так как теперь цех жестко связан с техкартой
     */
    public function checkAvailabilityInWorkshop(int $quantity = 1, int $workshopId = null): array
    {
        // Для обратной совместимости
        if ($workshopId && $workshopId !== $this->workshop_id) {
            throw new \InvalidArgumentException('Workshop ID must match tech card workshop');
        }
        
        return $this->checkAvailability($quantity);
    }

    /**
     * Проверить доступность ресурса во всех цехах
     * @deprecated используйте checkAvailability(), так как теперь цех жестко связан с техкартой
     */
    public function checkAvailabilityAllWorkshops(int $quantity = 1): array
    {
        return $this->checkAvailability($quantity);
    }

    /**
     * Зарезервировать ингредиенты для производства
     */
    public function reserveIngredients(int $quantity = 1, int $productionId, ?int $workshopId = null): bool
    {
        if ($workshopId && $workshopId !== $this->workshop_id) {
            throw new \InvalidArgumentException('Workshop ID must match tech card workshop');
        }

        try {
            app(OptimizedReservationService::class)->reserveIngredients(
                $this->id, 
                $quantity, 
                $productionId
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Подтвердить использование зарезервированных ингредиентов
     */
    public function confirmReservedIngredients(int $productionId): void
    {
        app(OptimizedReservationService::class)->confirmReservation($productionId);
    }

    /**
     * Отменить резервирование ингредиентов
     */
    public function cancelReservedIngredients(int $productionId): void
    {
        // Просто удаляем все резервирования для данного production_id
        ResourceStock::where('production_id', $productionId)
            ->where('status', 0)
            ->delete();
    }

    /**
     * Получить максимально возможное количество для производства
     */
    public function getMaxPossibleQuantity(): int
    {
        return $this->checkAvailability(1)['max_possible_quantity'];
    }

    /**
     * Получить список недостающих ингредиентов
     */
    public function getMissingIngredients(int $quantity = 1): array
    {
        return $this->checkAvailability($quantity)['missing_ingredients'];
    }

    /**
     * Получить список ингредиентов, которые можно переместить с основного склада
     */
    public function getTransferableFromPrimary(int $quantity = 1): array
    {
        return $this->checkAvailability($quantity)['can_transfer_from_primary'];
    }

    /**
     * Очистить кеш доступности для данной техкарты
     */
    public function clearAvailabilityCache(): void
    {
        app(OptimizedAvailabilityService::class)->clearCache($this->id);
    }

    /**
     * Рассчитывает время начала готовки для блюда
     */
    public function calculateStartTime($periodTime, $workshopShift = null)
    {
        $shift = $workshopShift ?? $this->getDefaultWorkshopShift();
        try {
            
            // Получаем время готовки
            $cookingTime = $this->cooking_time;
            
            if (!$cookingTime || (!isset($cookingTime['hours']) && !isset($cookingTime['minutes']))) {
                return [
                    'start_time' => $periodTime,
                    'cooking_duration_minutes' => 0,
                    'shift_info' => $shift
                ];
            }
            
            // Преобразуем время готовки в минуты
            $cookingMinutes = ($cookingTime['hours'] ?? 0) * 60 + ($cookingTime['minutes'] ?? 0);
            
            if ($cookingMinutes === 0) {
                return [
                    'start_time' => $periodTime,
                    'cooking_duration_minutes' => 0,
                    'shift_info' => $shift
                ];
            }
            
            // Преобразуем время периода в минуты от начала дня
            $periodMinutes = $this->timeToMinutes($periodTime);
            
            // Рассчитываем время начала (период - время готовки)
            $startMinutes = $periodMinutes - $cookingMinutes;
            
            // Проверяем границы рабочей смены
            $shiftStartMinutes = $this->timeToMinutes($shift['start']);
            $shiftEndMinutes = $this->timeToMinutes($shift['end']);
            
            $warningTime = false;
            $actualStartMinutes = $startMinutes;
            $conflictType = null;
            
            // Проверяем конфликты со сменой
            if ($startMinutes < $shiftStartMinutes) {
                $warningTime = true;
                $conflictType = 'before_shift';
            } elseif ($startMinutes > $shiftEndMinutes) {
                $warningTime = true;
                $conflictType = 'after_shift';
            }
            
            return [
                'start_time' => $this->minutesToTime($actualStartMinutes),
                'start_time_warning' => $warningTime ? $this->getShiftConflictMessage($conflictType, $this->minutesToTime($startMinutes), $shift) : null,
                'cooking_duration_minutes' => $cookingMinutes,
                'conflict_type' => $conflictType,
                'shift_info' => $shift
            ];
            
        } catch (\Exception $e) {
            \Log::error('Ошибка расчета времени начала готовки для техкарты ' . $this->id . ': ' . $e->getMessage());
            
            return [
                'start_time' => $periodTime,
                'start_time_warning' => 'Ошибка расчета времени',
                'cooking_duration_minutes' => 0,
                'shift_info' => $shift
            ];
        }
    }

    /**
     * Получает настройки смены по умолчанию
     */
    protected function getDefaultWorkshopShift()
    {
        // Можно настроить разные смены для разных цехов
        if ($this->workshop_id) {
            $workshopShifts = config('workshop.shifts_by_workshop', []);
            if (isset($workshopShifts[$this->workshop_id])) {
                return $workshopShifts[$this->workshop_id];
            }
        }
        
        return [
            'start' => config('workshop.shift.start', '06:00'),
            'end' => config('workshop.shift.end', '21:00')
        ];
    }

    /**
     * Преобразует время в минуты от начала дня
     */
    protected function timeToMinutes($timeString)
    {
        if (!$timeString) return 0;
        
        // Обрабатываем формат HH:MM:SS и HH:MM
        $parts = explode(':', $timeString);
        return (int)$parts[0] * 60 + (int)$parts[1];
    }

    /**
     * Преобразует минуты в строку времени HH:MM
     */
    protected function minutesToTime($minutes)
    {
        $hours = intval($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }

    /**
     * Получает сообщение о конфликте со сменой
     */
    protected function getShiftConflictMessage($conflictType, $originalStartTime, $shift)
    {
        switch ($conflictType) {
            case 'before_shift':
                return "Требуется начать готовку в {$originalStartTime}, но смена начинается в {$shift['start']}";
            
            case 'after_shift':
                return "Требуется начать готовку в {$originalStartTime}, но смена заканчивается в {$shift['end']}";
            
            default:
                return 'Конфликт с рабочим временем';
        }
    }

    /**
     * Проверяет, можно ли приготовить блюдо в указанное время
     */
    public function canCookAtTime($periodTime, $workshopShift = null)
    {
        $calculation = $this->calculateStartTime($periodTime, $workshopShift);
        return !$calculation['warning_time'];
    }

    /**
     * Получает рекомендуемое время периода для блюда
     */
    public function getRecommendedPeriodTime($workshopShift = null)
    {
        $shift = $workshopShift ?? $this->getDefaultWorkshopShift();
        $cookingTime = $this->cooking_time;
        
        if (!$cookingTime) {
            return $shift['start'];
        }
        
        $cookingMinutes = ($cookingTime['hours'] ?? 0) * 60 + ($cookingTime['minutes'] ?? 0);
        $shiftStartMinutes = $this->timeToMinutes($shift['start']);
        
        // Минимальное время периода = начало смены + время готовки
        $minPeriodMinutes = $shiftStartMinutes + $cookingMinutes;
        
        return $this->minutesToTime($minPeriodMinutes);
    }
}
