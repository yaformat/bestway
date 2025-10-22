<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use App\Models\StockConfirmation;

class StockConfirmationUpdated
{
    use Dispatchable, SerializesModels;

    public $stockConfirmation;

    public function __construct(StockConfirmation $stockConfirmation)
    {
        $this->stockConfirmation = $stockConfirmation;
    }
}