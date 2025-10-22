<?php

namespace App\Listeners;

use App\Events\StockTransferUpdated;
use App\Models\StockConfirmation;
use App\Models\StockConfirmationResource;

class CreateStockTransferConfirmation
{
    public function handle(StockTransferUpdated $event)
    {
        $stockTransfer = $event->stockTransfer;

        //если текущее перемещение не перешло в статус "Подтверждение" - пропускаем...
        if ($stockTransfer->status !== \App\Enums\StockActionStatusEnum::CONFIRMATION) return;

        $stockConfirmation = StockConfirmation::firstOrCreate(
            ['transfer_id' => $stockTransfer->id],
            [
                'organization_id' => $stockTransfer->organization_id,
                'user_id' => $stockTransfer->user_id,
                'stock_id' => $stockTransfer->stock_to_id,
                'status' => \App\Enums\StockActionStatusEnum::CONFIRMATION
            ]
        );

        // Извлекаем все RESOURCE связанные с stockTransfer и группируем их по resource_id
        $groupedResources = $stockTransfer->resources->groupBy('resource_id');
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