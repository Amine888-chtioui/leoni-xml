<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Segment extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class);
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