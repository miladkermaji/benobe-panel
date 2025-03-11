<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacation extends Model
{
    use HasFactory;

    // جدول مرتبط با مدل
    protected $table = 'vacations';

    // مشخص کردن فیلدهای قابل پر کردن
    protected $fillable = [
        'doctor_id',
        'clinic_id',
        'date',
        'start_time',
        'end_time',
        'is_full_day',
    ];

    /**
     * ارتباط با مدل دکتر (یک مرخصی متعلق به یک دکتر است).
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

}
