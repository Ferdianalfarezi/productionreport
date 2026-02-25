<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportProduction extends Model
{
    protected $table = 'report_productions';

    protected $fillable = [
        'report_id',
        'prod_date',
        'prod_start',
        'prod_finish',
        'part_no',
        'category',
        'qty_category',
        'process_no',
        'machine_no',
        'act_machine',
        'shift',
        'group_no',
        'dandori',
        'stroke',
        'qty_ok',
        'qty_ng',
        'code_ng',
        'remarks',
        'wt_gross',
        'wt_net',
        'lt_process',
        'lt_total',
        'gsph_theory',
        'gsph',
        'sph',
        'keterangan',
        'update_by',
        'update_time',
    ];
}