<?php

namespace App\Services;

use App\Models\DailyStat;
use App\Models\Machine;
use App\Models\MonthlyStat;
use App\Models\Segment;
use App\Models\WeeklyStat;
use App\Models\WorkOrder;
use App\Models\XmlFile;
use App\Models\YearlyStat;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class XmlImportService
{
    /**
     * Import XML file data into the database
     *
     * @param string $filePath
     * @param string $fileName
     * @return bool
     */
    public function importXml(string $filePath, string $fileName): bool
    {
        try {
            // Load XML file with proper options for large files
            $xml = simplexml_load_file($filePath, \SimpleXMLElement::class, LIBXML_NOCDATA | LIBXML_NOBLANKS);
            
            if (!$xml) {
                Log::error("Failed to parse XML file: $fileName");
                return false;
            }
            
            // Register the Crystal Report namespace
            $namespaces = $xml->getNamespaces(true);
            $hasNamespace = isset($namespaces['']);
            $prefix = $hasNamespace ? '' : '';
            
            // Extract report dates - we need to adapt our xpath queries to the specific XML structure
            $fromDate = $this->extractDateFromXml($xml, 'vonDate1');
            $toDate = $this->extractDateFromXml($xml, 'bisDate1');
            $printDate = $this->extractDateFromXml($xml, 'Field3');
            
            if (!$fromDate || !$toDate || !$printDate) {
                Log::error("Failed to extract dates from XML file: $fileName");
                return false;
            }
            
            // Extract total stop time
            $totalStopTime = $this->extractTotalStopTimeFromXml($xml);
            
            // Begin transaction
            DB::beginTransaction();
            
            // Create XML file record
            $xmlFile = XmlFile::create([
                'filename' => $fileName,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'print_date' => $printDate,
                'total_stop_time' => $totalStopTime,
                'imported_at' => now(),
            ]);
            
            // Process segments and work orders
            $this->processGroups($xml, $fromDate);
            
            // Update statistics
            $this->updateStats($fromDate);
            
            // Commit transaction
            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error importing XML file: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Extract date from XML using a more robust approach
     *
     * @param \SimpleXMLElement $xml
     * @param string $fieldName
     * @return Carbon|null
     */
    private function extractDateFromXml(\SimpleXMLElement $xml, string $fieldName): ?Carbon
    {
        try {
            // Try different XPath expressions to find the date
            $expressions = [
                "//Field[@Name='$fieldName']/Value",
                "//Field[@Name='$fieldName']/FormattedValue",
                "//Field[@FieldName='{?$fieldName}']/Value",
                "//Field[@FieldName='{?$fieldName}']/FormattedValue",
                "//Field[contains(@Name, '$fieldName')]/Value",
                "//Field[contains(@FieldName, '$fieldName')]/Value"
            ];
            
            foreach ($expressions as $expression) {
                $nodes = $xml->xpath($expression);
                if (!empty($nodes)) {
                    $dateStr = (string) $nodes[0];
                    
                    // Try to parse in different formats
                    $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'Y/m/d'];
                    foreach ($formats as $format) {
                        try {
                            $date = Carbon::createFromFormat($format, $dateStr);
                            if ($date) {
                                return $date;
                            }
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                    
                    // If we got here, try a generic parse
                    return Carbon::parse($dateStr);
                }
            }
            
            // Special case for the "Field3" which is the print date
            if ($fieldName === 'Field3') {
                $printDateNodes = $xml->xpath("//Field[@Name='Field3']/Value");
                if (!empty($printDateNodes)) {
                    $dateStr = (string) $printDateNodes[0];
                    return Carbon::parse($dateStr);
                }
            }
            
            // If we get here, we failed to find the date
            return null;
        } catch (\Exception $e) {
            Log::error("Error extracting date ($fieldName): " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Extract total stop time from XML
     *
     * @param \SimpleXMLElement $xml
     * @return float
     */
    private function extractTotalStopTimeFromXml(\SimpleXMLElement $xml): float
    {
        try {
            // Try different XPath expressions to find the total stop time
            $expressions = [
                "//Field[@FieldName='{#Total-stop-time}']/Value",
                "//Field[@Name='Totalstoptime1']/Value",
                "//Field[contains(@FieldName, 'Total-stop-time')]/Value",
                "//Field[contains(@Name, 'stoptime')]/Value"
            ];
            
            foreach ($expressions as $expression) {
                $nodes = $xml->xpath($expression);
                if (!empty($nodes)) {
                    return (float) $nodes[0];
                }
            }
            
            // If we couldn't find the total stop time, calculate it from work orders
            $stopTimeNodes = $xml->xpath("//Field[@FieldName='{Work_history1.Stop_time}']/Value");
            $totalStopTime = 0;
            
            foreach ($stopTimeNodes as $node) {
                $totalStopTime += (float) $node;
            }
            
            return $totalStopTime;
        } catch (\Exception $e) {
            Log::error("Error extracting total stop time: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Process groups in the XML file
     *
     * @param \SimpleXMLElement $xml
     * @param Carbon $reportDate
     * @return void
     */
    private function processGroups(\SimpleXMLElement $xml, Carbon $reportDate): void
    {
        try {
            // Process each Level 1 group (segments)
            $groups = $xml->xpath("//Group[@Level='1']");
            
            foreach ($groups as $group) {
                // Extract segment name
                $segmentNameNodes = $group->xpath(".//Field[@FieldName='GroupName ({Work_history1.POS_key})']/Value");
                
                if (empty($segmentNameNodes)) {
                    // Try an alternative XPath
                    $segmentNameNodes = $group->xpath(".//Field[@Name='GroupNamePoskey1']/Value");
                }
                
                if (empty($segmentNameNodes)) {
                    continue;
                }
                
                $segmentName = (string) $segmentNameNodes[0];
                
                // Find or create segment
                $segment = Segment::firstOrCreate(['name' => $segmentName]);
                
                // Process machines and work orders
                $this->processMachines($group, $segment, $reportDate);
            }
        } catch (\Exception $e) {
            Log::error("Error processing groups: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Process machines in a segment group
     *
     * @param \SimpleXMLElement $groupXml
     * @param Segment $segment
     * @param Carbon $reportDate
     * @return void
     */
    private function processMachines(\SimpleXMLElement $groupXml, Segment $segment, Carbon $reportDate): void
    {
        try {
            // Process each Level 2 group (machines)
            $machineGroups = $groupXml->xpath(".//Group[@Level='2']");
            
            foreach ($machineGroups as $machineGroup) {
                // Extract machine code and name
                $machineCodeNodes = $machineGroup->xpath(".//Field[@FieldName='GroupName ({Work_history1.MO_key})']/Value");
                $machineNameNodes = $machineGroup->xpath(".//Field[@FieldName='{Work_history1.MO_name}']/Value");
                
                if (empty($machineCodeNodes)) {
                    // Try alternative XPath
                    $machineCodeNodes = $machineGroup->xpath(".//Field[@Name='Field6']/Value");
                }
                
                if (empty($machineNameNodes)) {
                    // Try alternative XPath
                    $machineNameNodes = $machineGroup->xpath(".//Field[@Name='TDatumvon2']/Value");
                }
                
                if (empty($machineCodeNodes) || empty($machineNameNodes)) {
                    continue;
                }
                
                $machineCode = (string) $machineCodeNodes[0];
                $machineName = (string) $machineNameNodes[0];
                
                // Find or create machine
                $machine = Machine::firstOrCreate(
                    ['code' => $machineCode],
                    ['name' => $machineName, 'segment_id' => $segment->id]
                );
                
                // Process work orders
                $this->processWorkOrders($machineGroup, $machine, $reportDate);
            }
        } catch (\Exception $e) {
            Log::error("Error processing machines: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Process work orders for a machine
     *
     * @param \SimpleXMLElement $machineXml
     * @param Machine $machine
     * @param Carbon $reportDate
     * @return void
     */
    private function processWorkOrders(\SimpleXMLElement $machineXml, Machine $machine, Carbon $reportDate): void
    {
        try {
            // Process each Level 3 details (work orders)
            $details = $machineXml->xpath(".//Details[@Level='3']/Section");
            
            foreach ($details as $detail) {
                // Extract work order data
                $woKeyNodes = $detail->xpath(".//Field[@FieldName='{Work_history1.WO_key}']/Value");
                $woNameNodes = $detail->xpath(".//Field[@FieldName='{Work_history1.WO_name}']/Value");
                $stopTimeNodes = $detail->xpath(".//Field[@FieldName='{Work_history1.Stop_time}']/Value");
                $code1Nodes = $detail->xpath(".//Field[@FieldName='{Work_history1.Code1_key}']/Value");
                $code2Nodes = $detail->xpath(".//Field[@FieldName='{Work_history1.Code2_key}']/Value");
                $code3Nodes = $detail->xpath(".//Field[@FieldName='{Work_history1.Code3_key}']/Value");
                $workSupplierNodes = $detail->xpath(".//Field[@FieldName='{Transactions1.Work_supplier_key}']/Value");
                
                // Try alternative XPaths if needed
                if (empty($woKeyNodes)) {
                    $woKeyNodes = $detail->xpath(".//Field[@Name='WOKey1']/Value");
                }
                
                if (empty($woNameNodes)) {
                    $woNameNodes = $detail->xpath(".//Field[@Name='TTMOkey1']/Value");
                }
                
                if (empty($stopTimeNodes)) {
                    $stopTimeNodes = $detail->xpath(".//Field[@Name='TTDebitaccountkey1']/Value");
                }
                
                if (empty($code1Nodes)) {
                    $code1Nodes = $detail->xpath(".//Field[@Name='Code1key1']/Value");
                }
                
                if (empty($code2Nodes)) {
                    $code2Nodes = $detail->xpath(".//Field[@Name='Code2key1']/Value");
                }
                
                if (empty($code3Nodes)) {
                    $code3Nodes = $detail->xpath(".//Field[@Name='Code3key1']/Value");
                }
                
                if (empty($workSupplierNodes)) {
                    $workSupplierNodes = $detail->xpath(".//Field[@Name='WorkSupplierKey1']/Value");
                }
                
                if (empty($woKeyNodes) || empty($woNameNodes) || empty($stopTimeNodes)) {
                    continue;
                }
                
                $woKey = (string) $woKeyNodes[0];
                $woName = (string) $woNameNodes[0];
                $stopTime = (float) $stopTimeNodes[0];
                $code1 = !empty($code1Nodes) ? (string) $code1Nodes[0] : null;
                $code2 = !empty($code2Nodes) ? (string) $code2Nodes[0] : null;
                $code3 = !empty($code3Nodes) ? (string) $code3Nodes[0] : null;
                $workSupplier = !empty($workSupplierNodes) ? (string) $workSupplierNodes[0] : null;
                
                // Create or update work order
                WorkOrder::updateOrCreate(
                    [
                        'wo_key' => $woKey,
                        'machine_id' => $machine->id,
                        'report_date' => $reportDate
                    ],
                    [
                        'wo_name' => $woName,
                        'stop_time' => $stopTime,
                        'code1' => $code1,
                        'code2' => $code2,
                        'code3' => $code3,
                        'work_supplier' => $workSupplier,
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error("Error processing work orders: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update statistics after importing data
     *
     * @param Carbon $reportDate
     * @return void
     */
    private function updateStats(Carbon $reportDate): void
    {
        try {
            $this->updateDailyStats($reportDate);
            $this->updateWeeklyStats($reportDate);
            $this->updateMonthlyStats($reportDate);
            $this->updateYearlyStats($reportDate);
        } catch (\Exception $e) {
            Log::error("Error updating stats: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update daily statistics
     *
     * @param Carbon $date
     * @return void
     */
    private function updateDailyStats(Carbon $date): void
    {
        // Get all segments
        $segments = Segment::all();
        
        foreach ($segments as $segment) {
            // Get work orders for this segment and date
            $workOrders = WorkOrder::whereHas('machine', function ($query) use ($segment) {
                $query->where('segment_id', $segment->id);
            })->whereDate('report_date', $date)->get();
            
            if ($workOrders->isEmpty()) {
                continue;
            }
            
            // Calculate segment-level statistics
            $totalStopTime = $workOrders->sum('stop_time');
            $interventionsCount = $workOrders->count();
            
            // Update or create segment-level daily stats
            DailyStat::updateOrCreate(
                [
                    'date' => $date,
                    'segment_id' => $segment->id,
                    'machine_id' => null,
                ],
                [
                    'total_stop_time' => $totalStopTime,
                    'interventions_count' => $interventionsCount,
                ]
            );
            
            // Group work orders by machine
            $machineStats = $workOrders->groupBy('machine_id');
            
            foreach ($machineStats as $machineId => $machineWorkOrders) {
                $machineTotalStopTime = $machineWorkOrders->sum('stop_time');
                $machineInterventionsCount = $machineWorkOrders->count();
                
                // Update or create machine-level daily stats
                DailyStat::updateOrCreate(
                    [
                        'date' => $date,
                        'segment_id' => $segment->id,
                        'machine_id' => $machineId,
                    ],
                    [
                        'total_stop_time' => $machineTotalStopTime,
                        'interventions_count' => $machineInterventionsCount,
                    ]
                );
            }
        }
    }
    
    /**
     * Update weekly statistics
     *
     * @param Carbon $date
     * @return void
     */
    private function updateWeeklyStats(Carbon $date): void
    {
        $year = $date->year;
        $week = $date->weekOfYear;
        
        // Get all segments
        $segments = Segment::all();
        
        foreach ($segments as $segment) {
            // Calculate segment-level statistics
            $dailyStats = DailyStat::where('segment_id', $segment->id)
                ->whereNull('machine_id')
                ->whereYear('date', $year)
                ->where(function ($query) use ($date) {
                    $query->whereRaw('WEEK(date) = ?', [$date->weekOfYear]);
                })
                ->get();
            
            if ($dailyStats->isEmpty()) {
                continue;
            }
            
            $totalStopTime = $dailyStats->sum('total_stop_time');
            $interventionsCount = $dailyStats->sum('interventions_count');
            
            // Update or create segment-level weekly stats
            WeeklyStat::updateOrCreate(
                [
                    'year' => $year,
                    'week' => $week,
                    'segment_id' => $segment->id,
                    'machine_id' => null,
                ],
                [
                    'total_stop_time' => $totalStopTime,
                    'interventions_count' => $interventionsCount,
                ]
            );
            
            // Get all machines for this segment
            $machines = $segment->machines;
            
            foreach ($machines as $machine) {
                // Calculate machine-level statistics
                $machineDailyStats = DailyStat::where('segment_id', $segment->id)
                    ->where('machine_id', $machine->id)
                    ->whereYear('date', $year)
                    ->where(function ($query) use ($date) {
                        $query->whereRaw('WEEK(date) = ?', [$date->weekOfYear]);
                    })
                    ->get();
                
                if ($machineDailyStats->isEmpty()) {
                    continue;
                }
                
                $machineTotalStopTime = $machineDailyStats->sum('total_stop_time');
                $machineInterventionsCount = $machineDailyStats->sum('interventions_count');
                
                // Update or create machine-level weekly stats
                WeeklyStat::updateOrCreate(
                    [
                        'year' => $year,
                        'week' => $week,
                        'segment_id' => $segment->id,
                        'machine_id' => $machine->id,
                    ],
                    [
                        'total_stop_time' => $machineTotalStopTime,
                        'interventions_count' => $machineInterventionsCount,
                    ]
                );
            }
        }
    }
    
    /**
     * Update monthly statistics
     *
     * @param Carbon $date
     * @return void
     */
    private function updateMonthlyStats(Carbon $date): void
    {
        $year = $date->year;
        $month = $date->month;
        
        // Get all segments
        $segments = Segment::all();
        
        foreach ($segments as $segment) {
            // Calculate segment-level statistics
            $dailyStats = DailyStat::where('segment_id', $segment->id)
                ->whereNull('machine_id')
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get();
            
            if ($dailyStats->isEmpty()) {
                continue;
            }
            
            $totalStopTime = $dailyStats->sum('total_stop_time');
            $interventionsCount = $dailyStats->sum('interventions_count');
            
            // Update or create segment-level monthly stats
            MonthlyStat::updateOrCreate(
                [
                    'year' => $year,
                    'month' => $month,
                    'segment_id' => $segment->id,
                    'machine_id' => null,
                ],
                [
                    'total_stop_time' => $totalStopTime,
                    'interventions_count' => $interventionsCount,
                ]
            );
            
            // Get all machines for this segment
            $machines = $segment->machines;
            
            foreach ($machines as $machine) {
                // Calculate machine-level statistics
                $machineDailyStats = DailyStat::where('segment_id', $segment->id)
                    ->where('machine_id', $machine->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->get();
                
                if ($machineDailyStats->isEmpty()) {
                    continue;
                }
                
                $machineTotalStopTime = $machineDailyStats->sum('total_stop_time');
                $machineInterventionsCount = $machineDailyStats->sum('interventions_count');
                
                // Update or create machine-level monthly stats
                MonthlyStat::updateOrCreate(
                    [
                        'year' => $year,
                        'month' => $month,
                        'segment_id' => $segment->id,
                        'machine_id' => $machine->id,
                    ],
                    [
                        'total_stop_time' => $machineTotalStopTime,
                        'interventions_count' => $machineInterventionsCount,
                    ]
                );
            }
        }
    }
    
    /**
     * Update yearly statistics
     *
     * @param Carbon $date
     * @return void
     */
    private function updateYearlyStats(Carbon $date): void
    {
        $year = $date->year;
        
        // Get all segments
        $segments = Segment::all();
        
        foreach ($segments as $segment) {
            // Calculate segment-level statistics
            $monthlyStats = MonthlyStat::where('segment_id', $segment->id)
                ->whereNull('machine_id')
                ->where('year', $year)
                ->get();
            
            if ($monthlyStats->isEmpty()) {
                // Try to calculate from daily stats if monthly stats are not available
                $dailyStats = DailyStat::where('segment_id', $segment->id)
                    ->whereNull('machine_id')
                    ->whereYear('date', $year)
                    ->get();
                
                if ($dailyStats->isEmpty()) {
                    continue;
                }
                
                $totalStopTime = $dailyStats->sum('total_stop_time');
                $interventionsCount = $dailyStats->sum('interventions_count');
            } else {
                $totalStopTime = $monthlyStats->sum('total_stop_time');
                $interventionsCount = $monthlyStats->sum('interventions_count');
            }
            
            // Update or create segment-level yearly stats
            YearlyStat::updateOrCreate(
                [
                    'year' => $year,
                    'segment_id' => $segment->id,
                    'machine_id' => null,
                ],
                [
                    'total_stop_time' => $totalStopTime,
                    'interventions_count' => $interventionsCount,
                ]
            );
            
            // Get all machines for this segment
            $machines = $segment->machines;
            
            foreach ($machines as $machine) {
                // Calculate machine-level statistics from monthly stats
                $machineMonthlyStats = MonthlyStat::where('segment_id', $segment->id)
                    ->where('machine_id', $machine->id)
                    ->where('year', $year)
                    ->get();
                
                if ($machineMonthlyStats->isEmpty()) {
                    // Try to calculate from daily stats if monthly stats are not available
                    $machineDailyStats = DailyStat::where('segment_id', $segment->id)
                        ->where('machine_id', $machine->id)
                        ->whereYear('date', $year)
                        ->get();
                    
                    if ($machineDailyStats->isEmpty()) {
                        continue;
                    }
                    
                    $machineTotalStopTime = $machineDailyStats->sum('total_stop_time');
                    $machineInterventionsCount = $machineDailyStats->sum('interventions_count');
                } else {
                    $machineTotalStopTime = $machineMonthlyStats->sum('total_stop_time');
                    $machineInterventionsCount = $machineMonthlyStats->sum('interventions_count');
                }
                
                // Update or create machine-level yearly stats
                YearlyStat::updateOrCreate(
                    [
                        'year' => $year,
                        'segment_id' => $segment->id,
                        'machine_id' => $machine->id,
                    ],
                    [
                        'total_stop_time' => $machineTotalStopTime,
                        'interventions_count' => $machineInterventionsCount,
                    ]
                );
            }
        }
    }
}