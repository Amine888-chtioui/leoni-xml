<?php

namespace App\Http\Controllers;

use App\Models\MonthlyStat;
use App\Models\Segment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonthlyStatsController extends Controller
{
    /**
     * Display monthly statistics.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $year = $request->input('year', Carbon::now()->year);
        $month = $request->input('month', Carbon::now()->month);
        
        $segments = Segment::all();
        $selectedSegmentId = $request->input('segment_id');
        
        // Available years for dropdown
        $years = MonthlyStat::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        $query = MonthlyStat::with(['segment', 'machine'])
            ->where('year', $year)
            ->where('month', $month);
        
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
        
        // Format month name
        $monthName = Carbon::createFromDate($year, $month, 1)->format('F');
        
        return view('stats.monthly', compact(
            'year',
            'month',
            'monthName',
            'years',
            'segments',
            'selectedSegmentId',
            'segmentStats',
            'machineStats'
        ));
    }
}