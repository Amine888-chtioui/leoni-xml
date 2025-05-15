@extends('layouts.app')

@section('title', 'Statistiques Hebdomadaires')

@section('page-title', 'Statistiques Hebdomadaires')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <form action="{{ route('stats.weekly') }}" method="GET" class="flex flex-wrap items-end gap-4">
        <div>
            <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Année</label>
            <select 
                name="year" 
                id="year" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                @foreach($years as $yearOption)
                    <option value="{{ $yearOption }}" {{ $year == $yearOption ? 'selected' : '' }}>
                        {{ $yearOption }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label for="week" class="block text-sm font-medium text-gray-700 mb-1">Semaine</label>
            <select 
                name="week" 
                id="week" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                @foreach($weeks as $weekOption)
                    <option value="{{ $weekOption }}" {{ $week == $weekOption ? 'selected' : '' }}>
                        {{ $weekOption }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label for="segment_id" class="block text-sm font-medium text-gray-700 mb-1">Segment</label>
            <select 
                name="segment_id" 
                id="segment_id" 
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Tous les segments</option>
                @foreach($segments as $segment)
                    <option value="{{ $segment->id }}" {{ $selectedSegmentId == $segment->id ? 'selected' : '' }}>
                        {{ $segment->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div>
            <button 
                type="submit" 
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                Filtrer
            </button>
        </div>
    </form>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-700">Statistiques par segment - Semaine {{ $week }}, {{ $year }}</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Segment
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Temps d'arrêt total (h)
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nombre d'interventions
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Temps moyen par intervention (h)
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($segmentStats as $stat)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $stat->segment->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ number_format($stat->total_stop_time, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $stat->interventions_count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $stat->interventions_count > 0 ? number_format($stat->total_stop_time / $stat->interventions_count, 2) : 0 }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Aucune donnée disponible pour cette semaine
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-700">Statistiques par machine - Semaine {{ $week }}, {{ $year }}</h3>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Machine
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Segment
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Temps d'arrêt total (h)
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nombre d'interventions
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Temps moyen par intervention (h)
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($machineStats as $stat)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $stat->machine->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $stat->segment->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ number_format($stat->total_stop_time, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $stat->interventions_count }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $stat->interventions_count > 0 ? number_format($stat->total_stop_time / $stat->interventions_count, 2) : 0 }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Aucune donnée disponible pour cette semaine
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Weekly Chart -->
<div class="mt-6 bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Répartition par segment pour la semaine {{ $week }}, {{ $year }}</h3>
    <div>
        <canvas id="weeklyStopTimeChart" height="300"></canvas>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Prepare data for chart
        const segmentData = @json($segmentStats->map(function($stat) {
            return [
                'segment' => $stat->segment->name,
                'stop_time' => $stat->total_stop_time,
                'interventions' => $stat->interventions_count
            ];
        }));
        
        if (segmentData.length > 0) {
            const segmentNames = segmentData.map(item => item.segment);
            const stopTimes = segmentData.map(item => item.stop_time);
            const interventions = segmentData.map(item => item.interventions);
            
            // Create chart
            const ctx = document.getElementById('weeklyStopTimeChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: segmentNames,
                    datasets: [{
                        label: 'Temps d\'arrêt (heures)',
                        data: stopTimes,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    }, {
                        label: 'Nombre d\'interventions',
                        data: interventions,
                        backgroundColor: 'rgba(255, 99, 132, 0.6)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1,
                        type: 'line',
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            type: 'linear',
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Heures'
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            type: 'linear',
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Interventions'
                            },
                            grid: {
                                drawOnChartArea: false
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
        }
    });
</script>
@endsection