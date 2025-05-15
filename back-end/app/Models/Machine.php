<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'segment_id'];

    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function dailyStats(): HasMany
    {
        return $this->hasMany(DailyStat::class);
    }

    public function weeklyStats(): HasMany
    {
        return $this->hasMany(WeeklyStat::class);
    }

    public function monthlyStats(): HasMany
    {
        return $this->hasMany(MonthlyStat::class);
    }

    public function yearlyStats(): HasMany
    {
        return $this->hasMany(YearlyStat::class);
    }
}