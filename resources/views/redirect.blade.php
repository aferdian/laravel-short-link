<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $link->name ?? 'Redirecting...' }}</title>
    <x-favicon />
    <meta property="og:title" content="{{ $link->name ?? 'Redirecting...' }}">
    <meta property="og:description" content="{{ $link->description ?? 'Click to view the original content.' }}">
    <meta property="og:image" content="{{ $link->image ?? '' }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #e53935;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-900 text-white font-sans flex items-center justify-center min-h-screen">
    <div class="text-center">
        <h1 class="text-4xl font-bold mb-4">Redirecting you to {{ $serviceName }}...</h1>
        <p class="text-lg mb-8">We're trying to open the video in the {{ $serviceName }} app.</p>
        <div class="loader mx-auto mb-8"></div>
        <p class="text-gray-400">
            If nothing happens, <a href="{{ $webUrl }}" class="text-blue-400 underline">click here to watch in your browser</a>.
        </p>
    </div>

    <script>
        function redirect() {
            var appUrl = '{{ $appUrl }}';
            var webUrl = '{{ $webUrl }}';

            var timeout = setTimeout(function () {
                window.location = webUrl;
            }, 1000);

            window.location = appUrl;

            window.onblur = function() {
                clearTimeout(timeout);
            };
        }

        window.onload = redirect;
    </script>
</body>
</html>