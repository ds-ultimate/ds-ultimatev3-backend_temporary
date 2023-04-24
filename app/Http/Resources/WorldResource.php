<?php

namespace App\Http\Resources;

class WorldResource extends CustomJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return array_merge($this->allowFields([
            "id",
            "name",
            "display_name",
            "ally_count",
            "player_count",
            "village_count",
            "active",
            "server__code",
            "url",
        ]), [
            'hasConfig' => $this->config !== null,
            'hasUnits' => $this->units !== null,
            'hasBuildings' => $this->buildings !== null,
            'sortType' => $this->sortType(),
            'maintenanceMode'=> $this->resource->maintananceMode
        ]);
    }
}
