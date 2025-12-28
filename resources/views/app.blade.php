<!DOCTYPE html>
<html lang="en" >
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Expnzo</title>
    <link rel="icon" type="image/png" href="/fav.ico" />
    @vite(['resources/css/app.css'])
    @vite(['resources/js/app.js'])
</head>
<body>
    <div id="app"></div>
</body>
    <script>
        window.__FLASH__ = @json(session()->only(['auth_success', 'auth_error']));
    </script>
</html>