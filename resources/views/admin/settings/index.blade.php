@extends('layouts.app')

@section('title', 'Pengaturan Batasan Sistem')
@section('page_title', 'Pengaturan Batasan Sistem')

@section('content')
<div class="w-full space-y-6">

    <!-- Top Navigation -->
    <div class="mb-4 pb-2 border-b border-slate-200">
        <a href="{{ route('dashboard') }}" class="btn-secondary btn-sm px-3.5 py-1.5 d-inline-flex align-items-center gap-1.5 fw-semibold shadow-xs rounded-full text-xs font-head transition active:scale-95">
            <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

    <div class="card-commerce p-6 sm:p-7 w-full">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <div class="divide-y divide-slate-100">
                @foreach($settings as $setting)
                    <div class="py-4 first:pt-0 last:pb-0 grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-6 items-start">
                        <div class="lg:col-span-4">
                            <label class="block text-xs font-bold text-retail-ink font-head">{{ $setting->label }}</label>
                            <span class="text-xs text-slate-400 font-mono">{{ $setting->key }}</span>
                            @if($setting->description)
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $setting->description }}</p>
                            @endif
                        </div>

                        <div class="lg:col-span-8">
                            @if($setting->type === 'number')
                                <input type="number" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" required
                                    class="w-full input-commerce font-mono font-bold">
                            @else
                                <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" required
                                    class="w-full input-commerce font-mono">
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-5 border-t border-slate-100 flex justify-end">
                <button type="submit" class="btn-brand py-2.5 px-6 text-xs rounded-full">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Pengaturan Sistem</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
