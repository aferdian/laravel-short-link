<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <x-favicon />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Select2 -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <style>
            .select2-container--default .select2-selection--multiple {
                border-color: #d1d5db;
                border-radius: 0.375rem;
            }
            .select2-container--default .select2-selection--multiple .select2-selection__choice {
                background-color: #e5e7eb;
                border-color: #d1d5db;
            }
            .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
                color: #9ca3af;
            }
            .select2-container--default.select2-container--focus .select2-selection--multiple {
                border-color: #a5b4fc;
                box-shadow: 0 0 0 3px rgb(199 210 254);
            }
            .select2-container .select2-selection--multiple {
                min-height: 2.625rem;
            }
            .select2-container--default .select2-selection--single {
                border-color: #d1d5db;
                border-radius: 0.375rem;
                height: 2.625rem;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                line-height: 2.625rem;
            }
            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 2.625rem;
            }
            .filter-link .select2-container--default .select2-selection--single {
                border-radius: 0.375rem 0 0 0.375rem;
                border-right: none;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
