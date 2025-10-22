<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use App\Models\StockTransfer;

class StockTransferUpdated
{
    use Dispatchable, SerializesModels;

    public $stockTransfer;

    public function __construct(StockTransfer $stockTransfer)
    {
        $this->stockTransfer = $stockTransfer;
    }
}