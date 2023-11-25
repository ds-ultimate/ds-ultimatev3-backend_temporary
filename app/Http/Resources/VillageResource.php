<?php

namespace App\Http\Resources;

class VillageResource extends CustomJsonResource
{
    private $exportOwner;
    private $exportOwnerAlly;
    
    public function __construct($resource, $exportOwner=true, $exportOwnerAlly=true) {
        parent::__construct($resource);
        $this->exportOwner = $exportOwner;
        $this->exportOwnerAlly = $exportOwnerAlly;
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
            $fields[] = "playerLatest__ally_id";
        }

        if($this->exportOwnerAlly) {
            $fields[] = "playerLatest__allyLatest__name";
            $fields[] = "playerLatest__allyLatest__tag";
        }

        return $this->allowFields($fields);
    }
}
