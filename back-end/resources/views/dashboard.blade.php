@extends('layouts.app')

@section('title', 'Tableau de Bord')

@section('page-title', 'Tableau de Bord')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- XML Files Count -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Fichiers XML importés</p>
                <p class="text-2xl font-semibold text-gray-700">{{ $xmlFilesCount }}</p>
            </div>
        </div>
    </div>

    <!-- Segments Count -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Segments</p>
                <p class="text-2xl font-semibold text-gray-700">{{ $segmentsCount }}</p>
            </div>
        </div>
    </div>

    <!-- Machines Count -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-gray-500 text-sm">Machines</p>
                <p class="text-2xl font-semibold text-gray-700">{{ $machinesCount }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Latest Imports -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Derniers fichiers importés</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom du fichier</th>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Période</th>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Importé le</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($latestImports as $import)
                    <tr>
                        <td class="py-2 px-4 border-b border-gray-200">{{ $import->filename }}</td>
                        <td class="py-2 px-4 border-b border-gray-200">{{ $import->from_date->format('d/m/Y') }} - {{ $import->to_date->format('d/m/Y') }}</td>
                        <td class="py-2 px-4 border-b border-gray-200">{{ $import->imported_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                    
                    @if($latestImports->isEmpty())
                    <tr>
                        <td colspan="3" class="py-2 px-4 border-b border-gray-200 text-center text-gray-500">Aucun fichier importé</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Segments by Stop Time -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Segments avec le plus de temps d'arrêt</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Segment</th>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Année</th>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Temps d'arrêt (h)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topSegmentsByStopTime as $stat)
                    <tr>
                        <td class="py-2 px-4 border-b border-gray-200">{{ $stat->name }}</td>
                        <td class="py-2 px-4 border-b border-gray-200">{{ $stat->year }}</td>
                        <td class="py-2 px-4 border-b border-gray-200">{{ number_format($stat->total_stop_time, 2) }}</td>
                    </tr>
                    @endforeach
                    
                    @if($topSegmentsByStopTime->isEmpty())
                    <tr>
                        <td colspan="3" class="py-2 px-4 border-b border-gray-200 text-center text-gray-500">Aucune donnée disponible</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Top Machines by Stop Time -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Machines avec le plus de temps d'arrêt</h3>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Machine</th>
                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Segment</th>
                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Année</th>
                    <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Temps d'arrêt (h)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topMachinesByStopTime as $stat)
                <tr>
                    <td class="py-2 px-4 border-b border-gray-200">{{ $stat->name }}</td>
                    <td class="py-2 px-4 border-b border-gray-200">{{ $stat->segment_name }}</td>
                    <td class="py-2 px-4 border-b border-gray-200">{{ $stat->year }}</td>
                    <td class="py-2 px-4 border-b border-gray-200">{{ number_format($stat->total_stop_time, 2) }}</td>
                </tr>
                @endforeach
                
                @if($topMachinesByStopTime->isEmpty())
                <tr>
                    <td colspan="4" class="py-2 px-4 border-b border-gray-200 text-center text-gray-500">Aucune donnée disponible</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<!-- Quick Stats Chart -->
<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Aperçu graphique</h3>
    <div>
        <canvas id="stopTimeChart" height="300"></canvas>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Prepare data for chart
        const segmentNames = @json($topSegmentsByStopTime->pluck('name'));
        const stopTimes = @json($topSegmentsByStopTime->pluck('total_stop_time'));
        
        // Create chart
        const ctx = document.getElementById('stopTimeChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: segmentNames,
                datasets: [{
                    label: 'Temps d\'arrêt (heures)',
                    data: stopTimes,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Heures'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Segments'
                        }
                    }
                }
            }
        });
    });
</script>
@endsection