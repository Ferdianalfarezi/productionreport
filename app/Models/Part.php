<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Part extends Model
{
    protected $table = 'parts';

    protected $fillable = [
        'part_no_child',
        'line',
        'qty_kbn',
        'category',
        'qty_category',
        'update_by',
    ];
}
