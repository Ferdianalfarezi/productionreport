<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    protected $table = 'break_times';

    protected $fillable = [
        'break_name',
        'shift',
        'break_start',
        'duration',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'shift'     => 'integer',
        'duration'  => 'integer',
    ];

    /**
     * Scope: hanya yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: filter per shift (atau yang berlaku semua shift)
     */
    public function scopeForShift($query, int $shift)
    {
        return $query->where(function ($q) use ($shift) {
            $q->where('shift', $shift)->orWhereNull('shift');
        });
    }

    /**
     * break_start dalam menit absolut sejak 00:00
     */
    public function getBreakStartMinAttribute(): int
    {
        [$h, $m] = array_map('intval', explode(':', $this->break_start));
        return $h * 60 + $m;
    }

    /**
     * break_end dalam menit absolut sejak 00:00
     */
    public function getBreakEndMinAttribute(): int
    {
        return $this->break_start_min + $this->duration;
    }
}