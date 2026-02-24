<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineConfig extends Model
{
    use HasFactory;

    protected $table = 'line_configs';

    protected $fillable = [
        'line',
        'mesin',
        'update_by',
    ];
}