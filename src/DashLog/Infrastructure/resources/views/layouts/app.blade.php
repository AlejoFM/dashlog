<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - DashLog</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('dashlog.index') }}">
                            <h1 class="text-xl font-bold">DashLog</h1>
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                        <a href="{{ route('dashlog.settings.show') }}"
                            class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                            Settings
                        </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-6">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html> 