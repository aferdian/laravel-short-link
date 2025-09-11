<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between md:items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4 md:mb-0">
                {{ __('Dashboard') }}
            </h2>
            <form method="GET" action="{{ route('dashboard') }}">
                <div class="flex">
                    <x-text-input id="date_range" class="block w-full" rounding="rounded-l-md" type="text" name="date_range" :value="request('date_range')" />
                    <x-primary-button rounding="rounded-r-md">
                        {{ __('Filter') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-slot>

    <div class="py-0 md:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white overflow-hidden">
                            <div class="p-6 text-gray-900">
                                <h3 class="text-lg font-medium text-gray-900">Total Links</h3>
                                <p class="mt-1 text-3xl font-semibold text-gray-700">{{ $totalLinks }}</p>
                            </div>
                        </div>
                        <div class="bg-white overflow-hidden">
                            <div class="p-6 text-gray-900">
                                <h3 class="text-lg font-medium text-gray-900">Total Clicks</h3>
                                <p class="mt-1 text-3xl font-semibold text-gray-700">{{ $totalClicks }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 bg-white overflow-hidden">
                        <div class="p-6 text-gray-900">
                            <canvas id="dailyClicksChart"></canvas>
                        </div>
                    </div>
                </div>

                @push('scripts')
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
                <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    flatpickr("#date_range", {
                        mode: "range",
                        dateFormat: "Y-m-d",
                        @php
                            $dateRange = request('date_range', now()->subDays(6)->format('Y-m-d') . ' to ' . now()->format('Y-m-d'));
                            $dates = str_contains($dateRange, ' to ') ? explode(' to ', $dateRange) : [$dateRange, $dateRange];
                        @endphp
                        defaultDate: ["{{ $dates[0] }}", "{{ $dates[1] }}"],
                    });

                    const dailyClicksCtx = document.getElementById('dailyClicksChart').getContext('2d');
                    new Chart(dailyClicksCtx, {
                        type: 'bar',
                        data: {
                            labels: @json($dailyClicks->keys()),
                            datasets: [{
                                label: 'Daily Clicks',
                                data: @json($dailyClicks->values()),
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                </script>
                @endpush
            </div>
        </div>
    </div>
</x-app-layout>
