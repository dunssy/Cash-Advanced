@extends('layouts.app')

@section('title', 'Pengaturan Batasan Sistem')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div>
        <div class="inline-flex items-center space-x-1.5 text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">
            <i data-lucide="sliders" class="w-4 h-4 text-retail-cyan"></i>
            <span>Konfigurasi & Parameter Bisnis (Dokumen Hal 6, 7, 9, 10)</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-bold text-retail-ink tracking-tight">Pengaturan Parameter Sistem</h1>
        <p class="text-xs text-slate-500 mt-0.5">Konfigurasi batas durasi CA, ambang persetujuan Direktur, batas keterlambatan, dan rekening bank refund.</p>
    </div>

    <div class="card-commerce p-6 sm:p-7">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <div class="divide-y divide-slate-100">
                @foreach($settings as $setting)
                    <div class="py-4 first:pt-0 last:pb-0 grid grid-cols-1 md:grid-cols-3 gap-3 items-start">
                        <div class="md:col-span-1">
                            <label class="block text-xs font-bold text-retail-ink">{{ $setting->label }}</label>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $setting->key }}</span>
                            @if($setting->description)
                                <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">{{ $setting->description }}</p>
                            @endif
                        </div>

                        <div class="md:col-span-2">
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
                <button type="submit" class="btn-primary">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Pengaturan Sistem</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection