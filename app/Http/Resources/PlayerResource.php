<?php

namespace App\Http\Resources;

use App\Util\BasicFunctions;

class PlayerResource extends CustomJsonResource
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
            "allyLatest__name",
            "allyLatest__tag",
        ]);
    }
}
