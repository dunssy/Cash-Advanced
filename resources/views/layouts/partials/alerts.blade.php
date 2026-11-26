{{-- Inline Alert Banners --}}
@if(isset($errors) && $errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3 shadow-xs rounded-3 border-0 border-start border-4 border-danger bg-danger-subtle text-danger-emphasis" role="alert">
        <div class="d-flex align-items-center mb-1">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2 flex-shrink-0 text-danger"></i>
            <div class="fw-bold small me-auto font-head">
                Terdapat kesalahan pengisian formulir:
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
        <ul class="mb-0 ps-4 small font-body">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-3 shadow-xs rounded-3 border-0 border-start border-4 border-success bg-success-subtle text-success-emphasis" role="alert">
        <i class="bi bi-check-circle-fill fs-5 me-2.5 flex-shrink-0 text-success"></i>
        <div class="small fw-medium me-auto">
            {{ session('success') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center mb-3 shadow-xs rounded-3 border-0 border-start border-4 border-warning bg-warning-subtle text-warning-emphasis" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5 me-2.5 flex-shrink-0 text-warning"></i>
        <div class="small fw-medium me-auto">
            {{ session('warning') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-3 shadow-xs rounded-3 border-0 border-start border-4 border-danger bg-danger-subtle text-danger-emphasis" role="alert">
        <i class="bi bi-x-circle-fill fs-5 me-2.5 flex-shrink-0 text-danger"></i>
        <div class="small fw-medium me-auto">
            {{ session('error') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show d-flex align-items-center mb-3 shadow-xs rounded-3 border-0 border-start border-4 border-info bg-info-subtle text-info-emphasis" role="alert">
        <i class="bi bi-info-circle-fill fs-5 me-2.5 flex-shrink-0 text-info"></i>
        <div class="small fw-medium me-auto">
            {{ session('info') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif
