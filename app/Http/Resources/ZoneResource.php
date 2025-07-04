<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ZoneResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'province_id' => $this->parent_id, // برای شهرها
            'cities' => self::collection($this->whenLoaded('children')), // برای استان‌ها
        ];
    }
}
