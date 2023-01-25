<?php

namespace App\Http\Resources;

class ServerResource extends CustomJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return $this->allowFields([
            "id",
            "code",
            "flag",
            "url",
            "active",
            "speed_active",
            "classic_active",
            "locale",
        ]);
    }
}
