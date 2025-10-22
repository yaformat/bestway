<?php
namespace App\Http\Responses;

class VideoElement extends Response
{
    public function toArray()
    {
        return [
            'id' => $this->data['id'],
            'filename' => $this->data['filename'],
            'url' => $this->data['url'],
        ];
    }
}