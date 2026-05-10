<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">

<div class="min-h-screen flex flex-col">

    <header class="bg-white shadow-sm">
        <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-800">System Dashboard</h1>
            <a href="/backstage"
               class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                Abmelden
            </a>
        </div>
    </header>

    <main class="flex-1 max-w-5xl mx-auto px-6 py-10 w-full">
        <p class="text-gray-700">Willkommen im Systembereich.</p>
    </main>

</div>

</body>
</html>
