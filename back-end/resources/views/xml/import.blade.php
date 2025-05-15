@extends('layouts.app')

@section('title', 'Importation XML')

@section('page-title', 'Importer un fichier XML')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <p class="mb-4 text-gray-600">
        Utilisez ce formulaire pour importer un nouveau fichier XML contenant des données de maintenance.
        Le système traitera automatiquement le fichier et mettra à jour les statistiques.
    </p>

    <form action="{{ route('xml.import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        
        <div>
            <label for="xml_file" class="block text-sm font-medium text-gray-700 mb-1">Fichier XML</label>
            <input 
                type="file" 
                name="xml_file" 
                id="xml_file" 
                accept=".xml"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                required
            >
            @error('xml_file')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        
        <div>
            <button 
                type="submit" 
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
                Importer le fichier
            </button>
        </div>
    </form>
</div>

<div class="mt-6 bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-700 mb-4">Instructions d'importation</h3>
    
    <div class="space-y-3 text-gray-600">
        <p>
            <strong>Format du fichier :</strong> Le système accepte uniquement les fichiers XML au format spécifique pour les rapports de maintenance.
        </p>
        
        <p>
            <strong>Structure attendue :</strong> Le fichier doit contenir des informations sur les segments, les machines et les interventions, structurées selon le format prédéfini.
        </p>
        
        <p>
            <strong>Taille maximale :</strong> 10 Mo
        </p>
        
        <p>
            <strong>Traitement des données :</strong> Après l'importation, le système :
        </p>
        
        <ul class="list-disc list-inside ml-4">
            <li>Extrait les données de segments, machines et interventions</li>
            <li>Enregistre les nouvelles entrées dans la base de données</li>
            <li>Met à jour les statistiques quotidiennes, hebdomadaires, mensuelles et annuelles</li>
            <li>Vous redirige vers le tableau de bord une fois le traitement terminé</li>
        </ul>
    </div>
</div>
@endsection