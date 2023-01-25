<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomJsonResource extends JsonResource
{
    public function allowFields($fields) {
        $result = [];
        foreach($fields as $f) {
            $result[$f] = $this->$f;
        }
        return $result;
    }
}
