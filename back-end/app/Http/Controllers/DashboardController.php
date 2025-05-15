<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\Segment;
use App\Models\XmlFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     *
     * @return View
     */
    public function index(): View
    {
        // Get counts
        $xmlFilesCount = XmlFile::count();
        $segmentsCount = Segment::count();
        $machinesCount = Machine::count();
        
        // Get latest imports
        $latestImports = XmlFile::orderBy('imported_at', 'desc')
            ->take(5)
            ->get();
        
        // Get top segments by stop time (yearly)
        $topSegmentsByStopTime = DB::table('yearly_stats')
            ->join('segments', 'yearly_stats.segment_id', '=', 'segments.id')
            ->whereNull('yearly_stats.machine_id')
            ->orderBy('yearly_stats.total_stop_time', 'desc')
            ->select('segments.name', 'yearly_stats.total_stop_time', 'yearly_stats.year')
            ->take(5)
            ->get();
        
        // Get top machines by stop time (yearly)
        $topMachinesByStopTime = DB::table('yearly_stats')
            ->join('machines', 'yearly_stats.machine_id', '=', 'machines.id')
            ->join('segments', 'yearly_stats.segment_id', '=', 'segments.id')
            ->whereNotNull('yearly_stats.machine_id')
            ->orderBy('yearly_stats.total_stop_time', 'desc')
            ->select('machines.name', 'segments.name as segment_name', 'yearly_stats.total_stop_time', 'yearly_stats.year')
            ->take(5)
            ->get();
        
        return view('dashboard', compact(
            'xmlFilesCount',
            'segmentsCount',
            'machinesCount',
            'latestImports',
            'topSegmentsByStopTime',
            'topMachinesByStopTime'
        ));
    }
}