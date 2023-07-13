<?php

namespace App\Http\Resources;

use Carbon\Carbon;

class ChangelogResource extends CustomJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $customFields = [];
        $customFields["created_at"] = Carbon::parse($this->created_at)->timestamp;
        switch ($this->icon) {
            case "fas fa-code":
                $customFields["icon"] = "code";
                break;
            case "fas fa-bug":
                $customFields["icon"] = "bug";
                break;
            default:
                $customFields["icon"] = "git";
        }
        
        return array_merge($this->allowFields([
            "id",
            "version",
            "title",
            "de",
            "en",
            "repository_html_url",
            "color",
        ]), $customFields);
    }
}
