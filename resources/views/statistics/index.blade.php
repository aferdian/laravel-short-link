<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between md:items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4 md:mb-0">
                {{ __('Statistics') }}
            </h2>
            <form method="GET" action="{{ route('statistics.index') }}" class="flex flex-col md:flex-row items-center md:space-x-4">
                <div class="w-full md:w-auto md:flex-grow mb-4 md:mb-0 md:min-w-[300px]">
                    <select id="filter" name="filter" class="block w-full">
                        <option value="">All</option>
                        <optgroup label="Links">
                            @foreach($userLinks as $link)
                                <option value="link-{{ $link->id }}" @if(request('filter') == "link-{$link->id}") selected @endif>{{ $link->name }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Categories">
                            @foreach($userCategories as $category)
                                <option value="category-{{ $category->id }}" @if(request('filter') == "category-{$category->id}") selected @endif>{{ $category->name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
                <div class="flex w-full md:w-auto">
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900">Total Links</h3>
                        <p class="mt-1 text-3xl font-semibold text-gray-700">{{ $totalLinks }}</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900">Total Clicks</h3>
                        <p class="mt-1 text-3xl font-semibold text-gray-700">{{ $totalClicks }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900">Links</h3>
                    <div class="h-64">
                        <canvas id="linkCreationsChart" style="width: 100%;"></canvas>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900">Clicks</h3>
                    <div class="h-64">
                        <canvas id="clicksChart" style="width: 100%;"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900">Top Links</h3>
                        <ul class="divide-y divide-gray-200">
                            @foreach($topLinks as $link)
                                <li class="py-4 flex justify-between items-center">
                                    <div>
                                        <a href="{{ route('links.edit', $link) }}" class="font-bold text-sm text-gray-900 underline">{{ $link->name }}</a>
                                        <p class="text-sm text-gray-500"><a href="{{ url($link->short_code) }}" target="_blank" class="hover:underline">{{ url($link->short_code) }}</a></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-semibold">{{ $link->clicks_count }}</p>
                                        <p class="text-sm text-gray-500">Clicks</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900">Locations</h3>
                        <ul class="divide-y divide-gray-200">
                            @foreach($locations as $location)
                                <li class="py-4 flex justify-between items-center">
                                    <span>{{ $location->location }}</span>
                                    <span class="font-semibold">{{ $location->total }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900">Browsers</h3>
                        <div class="h-64">
                            <canvas id="browsersChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900">Devices</h3>
                        <div class="h-64">
                            <canvas id="devicesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            $('#filter').select2({
                width: '100%'
            });
        });

        flatpickr("#date_range", {
            mode: "range",
            dateFormat: "Y-m-d",
            @php
                $dateRange = request('date_range', now()->subDays(29)->format('Y-m-d') . ' to ' . now()->format('Y-m-d'));
                $dates = str_contains($dateRange, ' to ') ? explode(' to ', $dateRange) : [$dateRange, $dateRange];
            @endphp
            defaultDate: ["{{ $dates[0] }}", "{{ $dates[1] }}"],
        });

        const linkCreationsCtx = document.getElementById('linkCreationsChart').getContext('2d');
        new Chart(linkCreationsCtx, {
            type: 'bar',
            data: {
                labels: @json($linkCreations->keys()),
                datasets: [{
                    label: 'Link Creations',
                    data: @json($linkCreations->values()),
                    borderColor: 'rgba(75, 192, 192, 1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        const clicksCtx = document.getElementById('clicksChart').getContext('2d');
        new Chart(clicksCtx, {
            type: 'bar',
            data: {
                labels: @json($clicks->keys()),
                datasets: [{
                    label: 'Clicks',
                    data: @json($clicks->values()),
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        const browsersCtx = document.getElementById('browsersChart').getContext('2d');
        new Chart(browsersCtx, {
            type: 'pie',
            data: {
                labels: @json($browsers->pluck('browser')),
                datasets: [{
                    data: @json($browsers->pluck('total')),
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        const devicesCtx = document.getElementById('devicesChart').getContext('2d');
        new Chart(devicesCtx, {
            type: 'pie',
            data: {
                labels: @json($devices->pluck('os')),
                datasets: [{
                    data: @json($devices->pluck('total')),
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>