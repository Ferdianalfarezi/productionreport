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
        'import_date',   // ← tambah
    ];

    protected $casts = [
        'calc_time'   => 'datetime',
        'import_date' => 'date',
    ];

    /**
     * Daftar tanggal yang punya data import (untuk date picker).
     * Return Collection of date string 'Y-m-d', descending.
     */
    public static function availableImportDates(): \Illuminate\Support\Collection
    {
        return static::selectRaw('DISTINCT DATE(import_date) as d')
            ->whereNotNull('import_date')
            ->orderByRaw('d DESC')
            ->pluck('d');
    }

    /**
     * Tanggal import terbaru.
     */
    public static function latestImportDate(): ?string
    {
        return static::whereNotNull('import_date')
            ->max('import_date');
    }

    /**
     * Ambil stock_store terbaru berdasarkan part_no_child + line
     * dengan filter import_date opsional.
     */
    public static function getStockStore(string $partNoChild, string $line, ?string $importDate = null): ?int
    {
        $q = static::where('part_no_child', $partNoChild)
            ->where('line', $line);

        if ($importDate) {
            $q->whereDate('import_date', $importDate);
        }

        $record = $q->orderBy('e_planning_id', 'desc')->first();

        return $record ? $record->stock_store : null;
    }
}