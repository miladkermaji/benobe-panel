<?php

namespace App\Models\Dr;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentGateway extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_gateways';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'title',
        'logo',
        'is_active',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array', // تنظیمات JSON رو به‌صورت آرایه کست می‌کنه
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Get the primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Scope a query to only include active gateways.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the active payment gateway.
     *
     * @return static|null
     */
    public static function getActiveGateway()
    {
        return static::active()->first();
    }

    /**
     * Boot the model.
     */
    protected static function booted()
    {
        static::saving(function ($gateway) {
            // مطمئن می‌شیم که فقط یه درگاه فعال باشه
            if ($gateway->is_active) {
                static::where('id', '!=', $gateway->id)->update(['is_active' => false]);
            }
        });

        static::updated(function ($gateway) {
            // اگه هیچ درگاهی فعال نباشه، زرین‌پال رو فعال کن
            if (!static::active()->exists()) {
                static::where('name', 'zarinpal')->update(['is_active' => true]);
            }
        });
    }

    /**
     * Check if this is the active gateway.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Get a specific setting value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function getSetting($key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Set a specific setting value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function setSetting($key, $value)
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        return $this;
    }
}