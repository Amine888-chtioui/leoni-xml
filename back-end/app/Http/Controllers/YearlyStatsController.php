<?php

namespace App\Http\Controllers;

use App\Models\Segment;
use App\Models\YearlyStat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class YearlyStatsController extends Controller
{
    /**
     * Display yearly statistics.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $year = $request->input('year', Carbon::now()->year);
        
        $segments = Segment::all();
        $selectedSegmentId = $request->input('segment_id');
        
        // Available years for dropdown
        $years = YearlyStat::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        $query = YearlyStat::with(['segment', 'machine'])
            ->where('year', $year);
        
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
        
        return view('stats.yearly', compact(
            'year',
            'years',
            'segments',
            'selectedSegmentId',
            'segmentStats',
            'machineStats'
        ));
    }
}