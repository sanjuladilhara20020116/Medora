<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Medora Hospital Management System"
    >

    <title>
        @yield('title', 'Medora HMS')
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>

<body
    data-page="@yield('page', 'guest')"
    class="min-h-screen bg-slate-50"
>

    @yield('content')

</body>

</html>