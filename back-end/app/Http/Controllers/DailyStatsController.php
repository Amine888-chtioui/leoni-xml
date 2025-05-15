<?php

namespace App\Http\Controllers;

use App\Models\DailyStat;
use App\Models\Machine;
use App\Models\Segment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyStatsController extends Controller
{
    /**
     * Display daily statistics.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $date = $request->input('date') 
            ? Carbon::parse($request->input('date')) 
            : Carbon::today();
        
        $segments = Segment::all();
        $selectedSegmentId = $request->input('segment_id');
        
        $query = DailyStat::with(['segment', 'machine'])
            ->where('date', $date->toDateString());
        
        if ($selectedSegmentId) {
            $query->where('segment_id', $selectedSegmentId);
        }
        
        // Get segment-level stats first (machine_id is null)
        $segmentStats = (clone $query)
            ->whereNull('machine_id')
            ->orderBy('total_stop_time', 'desc')
            ->get();
        
        // Get machine-level stats
        $machineStats = (clone $query)
            ->whereNotNull('machine_id')
            ->orderBy('total_stop_time', 'desc')
            ->get();
        
        return view('stats.daily', compact(
            'date',
            'segments',
            'selectedSegmentId',
            'segmentStats',
            'machineStats'
        ));
    }
}