<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.partials.head')
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">

        <!--begin::Header / Navbar Partition-->
        @include('layouts.partials.navbar')
        <!--end::Header / Navbar Partition-->

        <!--begin::Sidebar Partition-->
        @include('layouts.partials.sidebar')
        <!--end::Sidebar Partition-->

        <!--begin::App Main Content-->
        <main class="app-main">
            <!--begin::App Content Header (Breadcrumbs & Title)-->
            <div class="app-content-header py-3 no-print">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-sm-6">
                            <h3 class="mb-0 fw-bold font-head" style="color: #050A1E; font-size: 1.25rem;">
                                @yield('page_title', View::yieldContent('title', 'Dashboard'))
                            </h3>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end mb-0 small">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}" class="text-decoration-none" style="color: #FF3C00;">
                                        <i class="bi bi-house-door me-1"></i>Beranda
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">@yield('title', 'Dashboard')</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::App Content Header-->

            <!--begin::App Content-->
            <div class="app-content py-2 pb-5">
                <div class="container-fluid">
                    <!-- Flash Alerts Partition -->
                    @include('layouts.partials.alerts')

                    <!-- Page Main Content Slot -->
                    @yield('content')
                </div>
            </div>
            <!--end::App Content-->
        </main>
        <!--end::App Main Content-->

        <!--begin::Footer Partition-->
        @include('layouts.partials.footer')
        <!--end::Footer Partition-->

    </div>
    <!--end::App Wrapper-->

    <!--begin::Scripts Partition-->
    @include('layouts.partials.scripts')
    <!--end::Scripts Partition-->
</body>
</html>
