<?php

namespace App\Listeners;

use App\Events\StockConfirmationUpdated;
use App\Models\StockConfirmation;

class ConfirmStockConfirmation
{
    public function handle(StockConfirmationUpdated $event)
    {
        $stockConfirmation = $event->stockConfirmation;

        //если текущее подтверждение уже было в статусе "Подтверждено" - пропускаем...
        if ($stockConfirmation->getOriginal('status') === \App\Enums\StockActionStatusEnum::CONFIRMED) return;
        //если текущее подтверждение не перешло в статус "Подтверждено" - пропускаем...
        if ($stockConfirmation->status !== \App\Enums\StockActionStatusEnum::CONFIRMED) return;

        //stock supply logic
        if ($stockConfirmation->supply) {
            $stockConfirmation->supply->update(['status' => \App\Enums\StockActionStatusEnum::COMPLETED]);
            echo 'stock supply confirm logic';
            return;
        }

        //stock transfer logic
        if ($stockConfirmation->transfer) {
            $stockConfirmation->transfer->update(['status' => \App\Enums\StockActionStatusEnum::COMPLETED]);
            echo 'stock transfer confirm logic';
            return;
        }
    }
}