<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesin extends Model
{
    protected $fillable = [
        'line_machine',
        'machine_no',
        'tonage',
        'line',
        'gsph_theory',
        'remarks',
        'sw_line',
        'sw_no',
        'update_by',
        'update_time',
    ];

    protected function casts(): array
    {
        return [
            'update_time' => 'datetime',
        ];
    }
}