{{-- resources/views/layouts/main.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Fast Services Payroll')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- <link rel="icon" type="image/x-icon" href="{{ asset('img/logo.jpg') }}"> --}}

    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/main.js') }}"></script>

    @stack('styles')
</head>

<body class="layout-fixed sidebar-expand-lg">
<div class="app-wrapper">

    {{-- ╔══════════════════════════════════════════════════════╗
         ║  TOP NAVBAR                                          ║
         ╚══════════════════════════════════════════════════════╝ --}}
    <nav class="app-header navbar navbar-expand" style="background-color: #161616; border-bottom:1px solid #2e2e2e;">
        <div class="container-fluid">

            {{-- Sidebar toggle --}}
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="#" data-lte-toggle="sidebar" role="button">
                        <i class="bi bi-list fs-4" style="color: #C9A227;"></i>
                    </a>
                </li>
            </ul>

            {{-- Breadcrumb slot --}}
            <ul class="navbar-nav me-auto ms-2">
                <li class="nav-item d-flex align-items-center">
                    @yield('breadcrumb')
                </li>
            </ul>

        </div>
    </nav>

    {{-- ╔══════════════════════════════════════════════════════╗
         ║  SIDEBAR                                             ║
         ╚══════════════════════════════════════════════════════╝ --}}
    <aside class="app-sidebar shadow" style="background-color:#1a1a1a;">
        @include('components.sidebar')
    </aside>

    {{-- ╔══════════════════════════════════════════════════════╗
         ║  MAIN CONTENT                                        ║
         ╚══════════════════════════════════════════════════════╝ --}}
    <main class="app-main" style="background:#f5f4f0;">
        <div class="app-content pt-3">
            <div class="container-fluid">
                @include('components.alerts')
                @yield('content')
            </div>
        </div>
    </main>

</div>

{{-- Global scripts --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@stack('scripts')
</body>
</html>