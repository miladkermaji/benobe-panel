<?php
namespace App\Models\Admin\Dashboard\HomePage;

use App\Models\Doctor;
use App\Models\Hospital;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
