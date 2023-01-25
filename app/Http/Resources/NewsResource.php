<?php

namespace App\Http\Resources;

class NewsResource extends CustomJsonResource
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
            "order",
            "content_de",
            "content_en",
        ]);
    }
}
