<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'description' => $this->description,
            'doctor_id' => $this->doctor_id,
            'clinic' => $this->whenLoaded('clinic'),
            'prescription_insurance' => $this->whenLoaded('insurance'),
            'referral_code' => $this->referral_code,
            'price' => $this->price,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'transaction' => $this->whenLoaded('transaction'),
            'insulins' => $this->whenLoaded('insulins', function () {
                return $this->insulins->map(function ($insulin) {
                    return [
                        'id' => $insulin->id,
                        'name' => $insulin->name,
                        'count' => $insulin->pivot->count,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
