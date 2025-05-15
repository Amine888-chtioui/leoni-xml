<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Analyse de Maintenance') | Système de Suivi</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @yield('styles')
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-blue-600 text-white shadow-md">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold">Système d'Analyse de Maintenance</h1>
                    </div>
                    <nav>
                        <ul class="flex space-x-4">
                            <li>
                                <a href="{{ route('dashboard') }}" class="hover:underline {{ request()->routeIs('dashboard') ? 'font-bold' : '' }}">
                                    Tableau de bord
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('xml.import') }}" class="hover:underline {{ request()->routeIs('xml.import') ? 'font-bold' : '' }}">
                                    Importer XML
                                </a>
                            </li>
                            <li class="relative" x-data="{ open: false }">
                                <button 
                                    @click="open = !open" 
                                    class="hover:underline {{ request()->routeIs('stats.*') ? 'font-bold' : '' }}"
                                >
                                    Statistiques
                                </button>
                                <div 
                                    x-show="open" 
                                    @click.away="open = false"
                                    class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10"
                                >
                                    <a href="{{ route('stats.daily') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Quotidiennes
                                    </a>
                                    <a href="{{ route('stats.weekly') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Hebdomadaires
                                    </a>
                                    <a href="{{ route('stats.monthly') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Mensuelles
                                    </a>
                                    <a href="{{ route('stats.yearly') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Annuelles
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow">
            <div class="container mx-auto px-4 py-6">
                <!-- Page Title -->
                <div class="mb-6">
                    <h2 class="text-3xl font-semibold text-gray-800">@yield('page-title')</h2>
                </div>

                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Page Content -->
                @yield('content')
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white text-gray-600 py-4 border-t">
            <div class="container mx-auto px-4">
                <p class="text-center">&copy; {{ date('Y') }} Système d'Analyse de Maintenance. Tous droits réservés.</p>
            </div>
        </footer>
    </div>

    @yield('scripts')
</body>
</html>