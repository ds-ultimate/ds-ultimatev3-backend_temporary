<?php

namespace App\Http\Resources;

class AllyResource extends CustomJsonResource
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
            "allyID",
            "name",
            "tag",
            "member_count",
            "points",
            "village_count",
            "rank",
            "offBash",
            "offBashRank",
            "defBash",
            "defBashRank",
            "gesBash",
            "gesBashRank",
        ]);
    }
}
