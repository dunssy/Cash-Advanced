<!--begin::Sidebar-->
<aside class="app-sidebar shadow no-print" data-bs-theme="dark" style="background-color: #050A1E !important;">
    <!--begin::Sidebar Brand-->
    <div class="sidebar-brand border-bottom px-3.5" style="border-color: #161A2B !important; height: 52px; display: flex; align-items: center; justify-content: flex-start;">
        <a href="{{ route('dashboard') }}" class="brand-link d-flex align-items-center justify-content-start text-start text-decoration-none w-100" title="PT Technodrives Indonesia">
            <img src="{{ asset('assets/images/logo.png') }}" alt="PT Technodrives" class="brand-image-full d-inline-block" style="height: 28px; width: auto; object-fit: contain; max-width: 175px;">
            <img src="{{ asset('assets/images/logo-icon-white.png') }}" alt="PT Technodrives" class="brand-image-compact d-none" style="height: 28px; width: 28px; object-fit: contain;">
        </a>
    </div>
    <!--end::Sidebar Brand-->

    <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper">
        <!--begin::User Panel-->
        <div class="user-panel mt-1.5 pb-2.5 mb-1.5 d-flex align-items-center px-3.5 border-bottom" style="border-color: #161A2B !important;">
            <div class="image me-2 flex-shrink-0">
                @if(auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle shadow-sm border border-secondary" style="width: 38px; height: 38px; object-fit: cover;" alt="{{ auth()->user()->name }}">
                @else
                    <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px; background-color: #FF3C00; font-size: 13px;">
                        {{ auth()->user()->initials }}
                    </div>
                @endif
            </div>
            <div class="info text-truncate">
                <a href="{{ route('profile.edit') }}" class="d-block text-white fw-semibold text-decoration-none text-truncate font-head" title="{{ auth()->user()->name }}" style="font-size: 13px;">
                    {{ auth()->user()->name }}
                </a>
                <div class="d-flex align-items-center gap-1 mt-0.5">
                    <span class="badge {{ auth()->user()->isDirektur() ? 'text-bg-warning text-dark' : (auth()->user()->isFinance() ? 'text-bg-primary' : (auth()->user()->isSuperAdmin() ? 'text-bg-danger' : 'text-bg-secondary')) }}" style="font-size: 11px; text-transform: uppercase;">
                        {{ auth()->user()->role }}
                    </span>
                    @if(!empty(auth()->user()->departments))
                        @foreach(array_slice(auth()->user()->departments, 0, 1) as $dept)
                            <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size: 11px;" title="# {{ $dept }}"># {{ $dept }}</span>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
        <!--end::User Panel-->

        <!--begin::Sidebar Menu-->
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                
                <!-- Group 1: Menu Utama -->
                <li class="nav-header">MENU UTAMA</li>
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('cash-advances.index') }}" class="nav-link {{ request()->routeIs('cash-advances.*') && !request()->routeIs('cash-advances.create') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-receipt"></i>
                        <p>{{ auth()->user()->isKaryawan() ? 'CA Saya' : 'Daftar CA' }}</p>
                    </a>
                </li>
                @if(auth()->user()->canSubmitCashAdvance() && !auth()->user()->isFinance() && !auth()->user()->isDirektur())
                    <li class="nav-item">
                        <a href="{{ route('cash-advances.create') }}" class="nav-link {{ request()->routeIs('cash-advances.create') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-plus-circle text-success"></i>
                            <p>Ajukan CA Baru</p>
                        </a>
                    </li>
                @endif

                <!-- Group 2: Laporan (Direktur, Finance, Atasan, Karyawan) -->
                @if(auth()->user()->isFinance() || auth()->user()->isDirektur() || auth()->user()->isAtasan() || auth()->user()->isKaryawan())
                    <li class="nav-item {{ request()->routeIs('reports.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-file-earmark-bar-graph"></i>
                            <p>
                                {{ auth()->user()->isKaryawan() ? 'Laporan' : 'Laporan Manajemen' }}
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview ps-2">
                            <li class="nav-item">
                                <a href="{{ route('reports.rincian-ca') }}" class="nav-link {{ request()->routeIs('reports.rincian-ca') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-circle" style="font-size: 10px;"></i>
                                    <p>1. Rincian per CA</p>
                                </a>
                            </li>
                            @if(auth()->user()->isFinance() || auth()->user()->isDirektur())
                                <li class="nav-item">
                                    <a href="{{ route('reports.rekap-bulanan') }}" class="nav-link {{ request()->routeIs('reports.rekap-bulanan') ? 'active' : '' }}">
                                        <i class="nav-icon bi bi-circle" style="font-size: 10px;"></i>
                                        <p>2. Rekap Bulanan Pajak</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('reports.outstanding') }}" class="nav-link {{ request()->routeIs('reports.outstanding') ? 'active' : '' }}">
                                        <i class="nav-icon bi bi-circle" style="font-size: 10px;"></i>
                                        <p>3. CA Menggantung</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('reports.rekap-karyawan') }}" class="nav-link {{ request()->routeIs('reports.rekap-karyawan') ? 'active' : '' }}">
                                        <i class="nav-icon bi bi-circle" style="font-size: 10px;"></i>
                                        <p>4. Rekap per Karyawan</p>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                <!-- Group 3: Audit & Kontrol (Direktur, Finance, SuperAdmin) -->
                @if(auth()->user()->isDirektur() || auth()->user()->isFinance() || auth()->user()->isSuperAdmin())
                    <li class="nav-header mt-2">AUDIT & KONTROL</li>
                    <li class="nav-item">
                        <a href="{{ route('audit-logs.index') }}" class="nav-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-shield-check"></i>
                            <p>Audit Trail</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('soft-deletes.index') }}" class="nav-link {{ request()->routeIs('soft-deletes.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-archive"></i>
                            <p>Arsip Soft Delete</p>
                        </a>
                    </li>
                @endif

                <!-- Group 4: Administrasi IT (SuperAdmin) -->
                @if(auth()->user()->isSuperAdmin())
                    <li class="nav-item {{ request()->routeIs('admin.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-gear"></i>
                            <p>
                                Administrasi IT
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview ps-2">
                            <li class="nav-item">
                                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-circle" style="font-size: 10px;"></i>
                                    <p>Kelola User</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-circle" style="font-size: 10px;"></i>
                                    <p>Pengaturan Global</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                <!-- Bottom Sidebar Item: Logout -->
                <li class="nav-item mt-3 pt-2 border-top border-secondary-subtle">
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="nav-link text-danger border-0 bg-transparent w-100 text-start d-flex align-items-center gap-2">
                            <i class="nav-icon bi bi-box-arrow-right"></i>
                            <p>Logout</p>
                        </button>
                    </form>
                </li>
            </ul>
        </nav>
        <!--end::Sidebar Menu-->
    </div>
    <!--end::Sidebar Wrapper-->
</aside>
<!--end::Sidebar-->
