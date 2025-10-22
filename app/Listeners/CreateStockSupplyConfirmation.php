<?php

namespace App\Listeners;

use App\Events\StockSupplyUpdated;
use App\Models\StockConfirmation;
use App\Models\StockConfirmationResource;

class CreateStockSupplyConfirmation
{
    public function handle(StockSupplyUpdated $event)
    {
        $stockSupply = $event->stockSupply;

        //если текущая поставка не перешла в статус "Подтверждение" - пропускаем...
        if ($stockSupply->status !== \App\Enums\StockActionStatusEnum::CONFIRMATION) return;

        $stockConfirmation = StockConfirmation::firstOrCreate(
            ['supply_id' => $stockSupply->id],
            [
                'organization_id' => $stockSupply->organization_id, 
                'user_id' => $stockSupply->user_id,
                'stock_id' => $stockSupply->stock_id,
                'status' => \App\Enums\StockActionStatusEnum::CONFIRMATION
            ]
        );

        // Извлекаем все RESOURCE связанные с StockSupply и группируем их по resource_id
        $groupedResources = $stockSupply->resources->groupBy('resource_id');
        // Теперь, для каждой группы RESOURCE, мы создаем или обновляем соответствующий элемент StockConfirmationResource
        $groupedResources->each(function($resourceGroup, $resourceId) use ($stockConfirmation) {
            // Вычисляем сумму 'value' и 'price' для группы
            $sumValue = $resourceGroup->sum('value');
            $sumPrice = $resourceGroup->sum('price');
            // Мы используем метод updateOrCreate для создания нового элемента, если он не существует,
            // или обновления существующего. 'resource_id' и 'confirmation_id' используются как условия для
            // нахождения существующей записи. $sumValue и $sumPrice используются как значения для обновления.
            StockConfirmationResource::updateOrCreate(
                ['resource_id' => $resourceId, 'confirmation_id' => $stockConfirmation->id],
                ['value' => $sumValue, 'price' => $sumPrice]
            );
        });

    }
}