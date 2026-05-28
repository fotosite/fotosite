<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mandant Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">

<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-sm bg-white rounded-lg shadow-md px-8 py-8">

        <h1 class="text-xl font-semibold text-gray-800 mb-6">Mandant Dashboard</h1>

        <p class="text-sm text-gray-500 mb-8">Platzhalter — Inhalt folgt.</p>

        <form method="POST" action="{{ route('mandant.logout') }}">
            @csrf
            <button type="submit"
                    class="w-full flex justify-center py-2 px-4 rounded-md text-sm font-medium
                           text-white bg-gray-800 hover:bg-gray-700 transition-colors
                           focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                Abmelden
            </button>
        </form>

    </div>
</div>

</body>
</html>
