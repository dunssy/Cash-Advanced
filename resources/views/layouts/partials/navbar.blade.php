<!--begin::Header-->
<nav class="app-header navbar navbar-expand bg-white border-bottom shadow-xs no-print">
    <!--begin::Container-->
    <div class="container-fluid">
        <!--begin::Start Navbar Links (Sidebar Toggle & Title)-->
        <ul class="navbar-nav align-items-center">
            <li class="nav-item">
                <a class="nav-link text-dark py-1 px-2" data-lte-toggle="sidebar" href="#" role="button" title="Menu Navigasi">
                    <i class="bi bi-list fs-4"></i>
                </a>
            </li>
            <li class="nav-item d-none d-md-flex align-items-center ms-2">
                <span class="navbar-text small text-secondary">
                    <strong class="text-dark font-head">Cash Advance & Settlement</strong>
                    <span class="mx-1.5">&bull;</span>
                    <span class="fw-bold" style="color: #FF3C00;">PT. Technodrives Indonesia</span>
                </span>
            </li>
        </ul>
        <!--end::Start Navbar Links-->

        <!--begin::End Navbar Links (Notifications & User Profile Dropdown)-->
        <ul class="navbar-nav ms-auto align-items-center">
            @php
                $unreadCount = auth()->user() ? auth()->user()->unreadNotifications()->count() : 0;
            @endphp

            <!-- Notifications Dropdown -->
            <li class="nav-item dropdown me-2">
                <a class="nav-link position-relative text-dark py-1 px-2" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false" title="Pusat Notifikasi">
                    <i class="bi bi-bell fs-5"></i>
                    @if($unreadCount > 0)
                        <span class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 11px;">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow-lg border-0 rounded-3 p-0 mt-2">
                    <div class="px-3 py-2.5 bg-light border-bottom d-flex align-items-center justify-content-between rounded-top-3">
                        <span class="fw-bold text-dark small text-uppercase">Notifikasi ({{ $unreadCount }})</span>
                        @if($unreadCount > 0)
                            <form action="{{ route('notifications.read-all') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-link p-0 text-decoration-none small fw-semibold" style="color: #FF3C00; font-size: 12px;">
                                    Tandai semua dibaca
                                </button>
                            </form>
                        @endif
                    </div>
                    <div class="list-group list-group-flush" style="max-height: 280px; overflow-y: auto;">
                        @forelse(auth()->user()->unreadNotifications()->take(5)->get() as $notif)
                            <div class="list-group-item list-group-item-action p-3">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <strong class="small text-dark font-head">{{ $notif->title }}</strong>
                                    <span class="text-muted" style="font-size: 11px;">{{ $notif->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="small text-secondary mb-1" style="font-size: 12px; line-height: 1.4;">{{ $notif->message }}</p>
                                @if($notif->link)
                                    <form action="{{ route('notifications.read', $notif) }}" method="POST" class="m-0 mt-1">
                                        @csrf
                                        <button type="submit" class="btn btn-link p-0 text-decoration-none small fw-semibold d-inline-flex align-items-center gap-1" style="color: #FF3C00; font-size: 12px;">
                                            <span>Lihat detail</span>
                                            <i class="bi bi-arrow-right"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-muted py-4 small">
                                <i class="bi bi-check2-circle fs-3 text-secondary d-block mb-1"></i>
                                Tidak ada notifikasi baru
                            </div>
                        @endforelse
                    </div>
                    <div class="p-2 text-center bg-light border-top rounded-bottom-3">
                        <a href="{{ route('notifications.index') }}" class="small fw-semibold text-decoration-none text-dark">
                            Lihat Semua Notifikasi
                        </a>
                    </div>
                </div>
            </li>

            <!-- User Menu Dropdown -->
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2 py-1 px-2" data-bs-toggle="dropdown" aria-expanded="false">
                    @if(auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;" alt="{{ auth()->user()->name }}">
                    @else
                        <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center shadow-xs" style="width: 32px; height: 32px; background-color: #FF3C00; font-size: 12px;">
                            {{ auth()->user()->initials }}
                        </div>
                    @endif
                    <div class="d-none d-md-block text-start leading-tight">
                        <span class="d-block fw-bold text-dark small font-head text-truncate" style="max-width: 140px;">{{ auth()->user()->name }}</span>
                        <span class="badge {{ auth()->user()->isDirektur() ? 'text-bg-warning text-dark' : (auth()->user()->isFinance() ? 'text-bg-primary' : (auth()->user()->isSuperAdmin() ? 'text-bg-danger' : 'text-bg-secondary')) }}" style="font-size: 11px; text-transform: uppercase;">
                            {{ auth()->user()->role }}
                        </span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow-lg border-0 rounded-3 p-0 mt-2">
                    <!-- User Header -->
                    <li class="p-3 bg-light border-bottom text-center rounded-top-3">
                        @if(auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle mb-2 border border-2 border-white shadow-sm" style="width: 52px; height: 52px; object-fit: cover;" alt="{{ auth()->user()->name }}">
                        @else
                            <div class="rounded-circle text-white fw-bold d-flex align-items-center justify-content-center mx-auto mb-2 shadow-sm" style="width: 52px; height: 52px; background-color: #FF3C00; font-size: 18px;">
                                {{ auth()->user()->initials }}
                            </div>
                        @endif
                        <h6 class="mb-0 fw-bold text-dark font-head">{{ auth()->user()->name }}</h6>
                        <small class="text-muted d-block font-mono" style="font-size: 12px;">{{ auth()->user()->email }}</small>
                        <div class="mt-2 d-flex flex-wrap justify-content-center gap-1">
                            <span class="badge {{ auth()->user()->isDirektur() ? 'text-bg-warning text-dark' : (auth()->user()->isFinance() ? 'text-bg-primary' : 'text-bg-dark') }}" style="font-size: 11px; text-transform: uppercase;">
                                {{ auth()->user()->role }}
                            </span>
                            @foreach(auth()->user()->departments as $dept)
                                <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size: 11px;"># {{ $dept }}</span>
                            @endforeach
                        </div>
                    </li>
                    <!-- User Body / Links -->
                    <li class="p-2">
                        <a href="{{ route('profile.edit') }}" class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2 small">
                            <i class="bi bi-person-gear fs-6 text-secondary"></i>
                            <span>Pengaturan Profil & Biodata</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <!-- User Footer (Logout) -->
                    <li class="p-2">
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2 small text-danger fw-semibold">
                                <i class="bi bi-box-arrow-right fs-6"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
        <!--end::End Navbar Links-->
    </div>
    <!--end::Container-->
</nav>
<!--end::Header-->
