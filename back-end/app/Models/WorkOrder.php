<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'wo_key',
        'wo_name',
        'stop_time',
        'machine_id',
        'code1',
        'code2',
        'code3',
        'work_supplier',
        'report_date'
    ];

    protected $casts = [
        'stop_time' => 'decimal:2',
        'report_date' => 'date'
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}