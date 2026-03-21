<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AT - Intelligence Artificielle</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* Empêche le flash blanc au chargement sur certains navigateurs */
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="antialiased bg-white dark:bg-[#212121] transition-colors duration-300">
    @yield('content')

    <script>
        // Configuration globale d'Axios pour Laravel
        window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        let token = document.head.querySelector('meta[name="csrf-token"]');
        if (token) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        }
    </script>
</body>
</html>