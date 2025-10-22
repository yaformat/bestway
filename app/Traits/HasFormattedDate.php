<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Helpers\Helper;

trait HasFormattedDate
{
    public function initializeHasFormattedDate()
    {
        $this->append('formatted_date');
    }

    public function getFormattedDateAttribute()
    {
        return Helper::formatDate($this->created_at);
    }
    
}
