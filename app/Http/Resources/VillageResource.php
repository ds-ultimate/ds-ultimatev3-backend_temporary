<?php

namespace App\Http\Resources;

class VillageResource extends CustomJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $fields = [
            "villageID",
            "name",
            "x",
            "y",
            "points",
            "owner",
            "bonus_id",
        ];
        
        return $this->allowFields($fields);
    }
}
