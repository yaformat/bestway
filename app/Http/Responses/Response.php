<?php
namespace App\Http\Responses;

abstract class Response
{
    protected $data;

    public function __construct($data)
    {
        if (is_object($data)) {
            $data = $data->toArray();
        }

        $this->data = $data;
    }

    // Abstract function to ensure child classes must implement their own version
    abstract public function toArray();
}