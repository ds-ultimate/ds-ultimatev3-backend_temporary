<?php

namespace App\Http\Resources;

class AllyChangeResource extends CustomJsonResource
{
    private $exportVillage;
    
    public function __construct($resource) {
        parent::__construct($resource);
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $fields = [
            "player_id",
            "old_ally_id",
            "new_ally_id",
            "points",
            "created_at",
            "player__name",
            "ally_old__name",
            "ally_old__tag",
            "ally_new__name",
            "ally_new__tag",
        ];
        
        return $this->allowFields($fields);
    }
}
