<?php

namespace App\Http\Resources;

class ConquerResource extends CustomJsonResource
{
    private $exportVillage;
    
    public function __construct($resource, $exportVillage=false) {
        parent::__construct($resource);
        $this->exportVillage = $exportVillage;
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
            "id",
            "new_ally",
            "new_ally_name",
            "new_ally_tag",
            "new_owner",
            "new_owner_name",
            "old_ally",
            "old_ally_name",
            "old_ally_tag",
            "old_owner",
            "old_owner_name",
            "points",
            "timestamp",
            "village_id",
        ];
        
        if($this->exportVillage) {
            $fields[] = "village__name";
            $fields[] = "village__x";
            $fields[] = "village__y";
            $fields[] = "village__bonus_id";
        }
        
        return $this->allowFields($fields);
    }
}
