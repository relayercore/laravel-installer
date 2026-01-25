<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('installer.name', 'App') }} - Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '{{ config("installer.theme.primary", "#6366f1") }}',
                        'primary-dark': '{{ config("installer.theme.primary_dark", "#4f46e5") }}',
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .gradient-bg {
            background: linear-gradient(135deg, {{ config('installer.theme.primary', '#6366f1') }}, {{ config('installer.theme.primary_dark', '#4f46e5') }});
        }
    </style>
    @livewireStyles
</head>
<body class="bg-gray-100 min-h-screen">
    {{ $slot }}
    @livewireScripts
</body>
</html>
