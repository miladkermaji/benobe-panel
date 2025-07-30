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
            'clinic' => $this->whenLoaded('medicalCenter'),
            'insurances' => $this->whenLoaded('insurances', function () {
                $referralNames = ['سلامت همگانی(ایرانیان)', 'کمیته امداد', 'سایر اقشار', 'بهزیستی'];
                return $this->insurances->map(function ($insurance) use ($referralNames) {
                    $parent = $insurance->relationLoaded('parent') ? $insurance->parent : null;
                    $needsReferral = false;
                    if ($parent && $parent->id == 3 && in_array($insurance->name, $referralNames)) {
                        $needsReferral = true;
                    }
                    return [
                        'id' => $insurance->id,
                        'name' => $insurance->name,
                        'parent_id' => $parent ? $parent->id : null,
                        'parent_name' => $parent ? $parent->name : null,
                        'needs_referral_code' => $needsReferral,
                        'referral_code' => $insurance->pivot->referral_code,
                    ];
                });
            }),
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
            'tracking_code' => $this->tracking_code,
            'doctor_description' => $this->doctor_description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
