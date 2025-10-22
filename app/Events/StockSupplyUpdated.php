<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use App\Models\StockSupply;

class StockSupplyUpdated
{
    use Dispatchable, SerializesModels;

    public $stockSupply;

    public function __construct(StockSupply $stockSupply)
    {
        $this->stockSupply = $stockSupply;
    }
}