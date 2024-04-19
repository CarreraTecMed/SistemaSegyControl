<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" type="image/png" href="{{ asset('/images/logoTecMed.png') }}">
        {{-- <link rel="shortcut icon" sizes="192x192" href="{{ asset('/images/logoTecMed.png') }}"> --}}
        <title>TecMed Documentacion</title>

        @viteReactRefresh

        @vite("resources/app/main.jsx")

    </head>

    <body>
        <div id="root"></div>
    </body>

</html>