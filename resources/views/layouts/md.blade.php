<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!--  Fonts and icons     -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.8/css/all.css">

    <!-- Styles -->
    <link href="{{ asset('css/material-kit.css') }}" rel="stylesheet">
    <link href="{{ asset('css/core.css') }}" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body class="index-page">
    <div id="app">
        <template><main-navbar :color-on-scroll="400" /></template>
        <div class="wrapper">
            <parallax class="page-header header-filter header-background">
                <div class="md-layout">
                    <div class="md-layout-item">
                        <div class="image-wrapper">
                            <div class="brand">
                                <h1>Mabors!</h1>
                                <h3>The description for Mabors! portal site.</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </parallax>
            <main class="py-4">
                @yield('content')
            </main>
        </div>
    </div>
</body>
<!-- Javascript -->
<script src="{{ asset('js/app.js') }}"></script>
</html>
