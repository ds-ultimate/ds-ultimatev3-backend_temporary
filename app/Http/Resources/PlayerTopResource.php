<?php

namespace App\Http\Resources;

class PlayerTopResource extends CustomJsonResource
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
            "playerID",
            "name",
            "village_count_top",
            "village_count_date",
            "points_top",
            "points_date",
            "rank_top",
            "rank_date",
            "offBash_top",
            "offBash_date",
            "offBashRank_top",
            "offBashRank_date",
            "defBash_top",
            "defBash_date",
            "defBashRank_top",
            "defBashRank_date",
            "supBash_top",
            "supBash_date",
            "supBashRank_top",
            "supBashRank_date",
            "gesBash_top",
            "gesBash_date",
            "gesBashRank_top",
            "gesBashRank_date",
        ];
        
        return $this->allowFields($fields);
    }
}
