<?php

namespace App\Http\Controllers;

use App\Models\Segment;
use App\Models\WeeklyStat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WeeklyStatsController extends Controller
{
    /**
     * Display weekly statistics.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $year = $request->input('year', Carbon::now()->year);
        $week = $request->input('week', Carbon::now()->week);
        
        $segments = Segment::all();
        $selectedSegmentId = $request->input('segment_id');
        
        // Available years for dropdown
        $years = WeeklyStat::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        // Available weeks for selected year
        $weeks = WeeklyStat::where('year', $year)
            ->select('week')
            ->distinct()
            ->orderBy('week')
            ->pluck('week');
        
        $query = WeeklyStat::with(['segment', 'machine'])
            ->where('year', $year)
            ->where('week', $week);
        
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
        
        return view('stats.weekly', compact(
            'year',
            'week',
            'years',
            'weeks',
            'segments',
            'selectedSegmentId',
            'segmentStats',
            'machineStats'
        ));
    }
}