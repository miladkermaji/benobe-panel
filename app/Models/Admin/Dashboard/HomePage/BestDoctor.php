<?php

namespace App\Models\Admin\Dashboard\HomePage;

use App\Models\Hospital;
use App\Models\Dr\Doctor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BestDoctor extends Model
{
    use HasFactory;

    protected $fillable = ['doctor_id', 'hospital_id', 'best_doctor', 'best_consultant'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}
