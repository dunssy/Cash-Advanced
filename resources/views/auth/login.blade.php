<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Cash Advance & Settlement PT Technodrives</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gradient-to-br from-slate-900 via-sky-950 to-slate-900 min-h-screen flex items-center justify-center p-4 font-sans text-slate-800">
    <div class="max-w-md w-full">
        <!-- Logo and Title -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-sky-500/20 border border-sky-400/30 text-sky-400 shadow-xl mb-3">
                <i data-lucide="wallet-cards" class="w-8 h-8"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">PT Technodrives</h1>
            <p class="text-sky-300 text-sm font-medium">Sistem Cash Advance & Settlement</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8 border border-slate-100">
            @if(session('info'))
                <div class="mb-4 bg-sky-50 border-l-4 border-sky-500 p-3 rounded-r text-xs text-sky-800">
                    {{ session('info') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-rose-50 border-l-4 border-rose-500 p-3 rounded-r text-xs text-rose-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Kantor</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </span>
                        <input type="email" name="email" value="{{ old('email', 'karyawan@technodrives.co.id') }}" required
                            class="w-full pl-9 pr-3 py-2.5 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:bg-white outline-none transition"
                            placeholder="nama@technodrives.co.id">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </span>
                        <input type="password" name="password" value="password" required
                            class="w-full pl-9 pr-3 py-2.5 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:bg-white outline-none transition"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-600">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        <span>Ingat saya</span>
                    </label>
                    <span class="text-slate-400">(Password default: password)</span>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 bg-sky-600 hover:bg-sky-700 text-white font-semibold text-sm rounded-xl shadow-md hover:shadow-lg transition flex items-center justify-center space-x-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Masuk ke Sistem</span>
                </button>
            </form>

            <!-- Quick Demo Login Switcher -->
            <div class="mt-6 pt-6 border-t border-slate-100">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider text-center mb-3">
                    Akses Cepat Demo (1-Click Login):
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($demoUsers as $demo)
                        <form action="{{ route('quick-login') }}" method="POST">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $demo->id }}">
                            <button type="submit" class="w-full text-left p-2 rounded-lg border border-slate-200 hover:border-sky-400 hover:bg-sky-50 transition flex items-center space-x-2">
                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0
                                    {{ $demo->role === 'direktur' ? 'bg-amber-500' : '' }}
                                    {{ $demo->role === 'finance' ? 'bg-purple-500' : '' }}
                                    {{ $demo->role === 'atasan' ? 'bg-blue-500' : '' }}
                                    {{ $demo->role === 'karyawan' ? 'bg-emerald-500' : '' }}
                                    {{ $demo->role === 'super_admin' ? 'bg-rose-500' : '' }}
                                "></span>
                                <div class="overflow-hidden">
                                    <span class="block text-xs font-bold text-slate-800 truncate">{{ $demo->name }}</span>
                                    <span class="block text-[10px] text-slate-500 uppercase font-semibold">{{ $demo->role }}</span>
                                </div>
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-slate-400">
            &copy; 2026 PT Technodrives Indonesia
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
