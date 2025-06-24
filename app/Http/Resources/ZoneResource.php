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
            'cities' => $this->when($this->level == 1, function () {
                return ZoneResource::collection(
                    \App\Models\Zone::where('parent_id', $this->id)
                        ->where('level', 2)
                        ->where('status', 1)
                        ->get()
                );
            }, []),
        ];
    }
}
