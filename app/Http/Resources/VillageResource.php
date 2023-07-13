<?php

namespace App\Http\Resources;

class VillageResource extends CustomJsonResource
{
    private $exportOwner;
    
    public function __construct($resource, $exportOwner=true) {
        parent::__construct($resource);
        $this->exportOwner = $exportOwner;
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
            "villageID",
            "name",
            "x",
            "y",
            "points",
            "owner",
            "bonus_id",
        ];
        
        if($this->exportOwner) {
            $fields[] = "playerLatest__name";
        }
        
        return $this->allowFields($fields);
    }
}
