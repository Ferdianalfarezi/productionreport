<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EPlanningStock extends Model
{
    protected $table = 'e_planning_stocks';

    protected $fillable = [
        'e_planning_id',
        'calc_prod',
        'lt_prod',
        'judgement',
        'part_no_fg',
        'part_no_parent',
        'part_no_child',
        'store',
        'process',
        'line',
        'rack_no',
        'seq_calc',
        'qty_kbn',
        'qty_consume',
        'qty_lot',
        'stock_min',
        'stock_max',
        'stock_prod',
        'stock_store',
        'status',
        'calc_by',
        'calc_time',
    ];

    protected $casts = [
        'calc_time' => 'datetime',
    ];

    /**
     * Ambil stock_store terbaru berdasarkan part_no_child + line
     * (berdasarkan e_planning_id terbesar = data terbaru)
     */
    public static function getStockStore(string $partNoChild, string $line): ?int
    {
        $record = static::where('part_no_child', $partNoChild)
            ->where('line', $line)
            ->orderBy('e_planning_id', 'desc')
            ->first();

        return $record ? $record->stock_store : null;
    }
}