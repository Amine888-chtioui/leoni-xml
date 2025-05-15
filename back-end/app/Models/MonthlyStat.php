<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'segment_id',
        'machine_id',
        'total_stop_time',
        'interventions_count'
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'total_stop_time' => 'decimal:2',
        'interventions_count' => 'integer'
    ];

    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}