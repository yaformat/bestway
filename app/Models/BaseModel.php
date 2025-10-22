<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    public static function sortings()
    {
        return \App\Presets\Sortings\DefaultSortings::sortings();
    }

    public static function filters()
    {
        return [];
    }

    public function toArray() 
    {
        $data = parent::toArray();
        if (isset($this->action_status)) {
            $data['status'] = $this->action_status;
            unset($data['action_status']);
        }
        return $data;
    }

}