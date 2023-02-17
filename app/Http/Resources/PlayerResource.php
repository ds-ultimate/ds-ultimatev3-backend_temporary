<?php

namespace App\Http\Resources;

class PlayerResource extends CustomJsonResource
{
    private $exportAlly;
    
    public function __construct($resource, $exportAlly=true) {
        parent::__construct($resource);
        $this->exportAlly = $exportAlly;
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
            "playerID",
            "name",
            "ally_id",
            "village_count",
            "points",
            "rank",
            "offBash",
            "offBashRank",
            "defBash",
            "defBashRank",
            "supBash",
            "supBashRank",
            "gesBash",
            "gesBashRank",
        ];
        
        if($this->exportAlly) {
            $fields[] = "allyLatest__name";
            $fields[] = "allyLatest__tag";
        }
        
        return $this->allowFields($fields);
    }
}
