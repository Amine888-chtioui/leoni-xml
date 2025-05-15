<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XmlFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'from_date',
        'to_date',
        'print_date',
        'total_stop_time',
        'imported_at'
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'print_date' => 'date',
        'total_stop_time' => 'decimal:2',
        'imported_at' => 'datetime'
    ];
}