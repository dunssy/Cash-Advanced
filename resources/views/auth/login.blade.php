<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Cash Advance & Settlement PT Technodrives</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Verdana', 'Geneva', 'Tahoma', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        retail: {
                            orange: '#FF9900',
                            orangeHover: '#E68A00',
                            ink: '#232F3E',
                            darkInk: '#131921',
                            cyan: '#00A8E1',
                            surface: '#DDDDDD',
                        }
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: Verdana, Geneva, Tahoma, sans-serif;
        }
        .btn-primary {
            background-color: #FF9900;
            color: #131921;
            font-weight: 600;
            border-radius: 3px;
            padding: 11px 18px;
            font-size: 0.875rem;
            border: 1px solid #D68100;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 200ms ease-out;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .btn-primary:hover {
            background-color: #E68A00;
            box-shadow: 0 3px 8px rgba(255, 153, 0, 0.35);
        }
        .btn-primary:active {
            transform: translateY(1px);
        }
        .input-commerce {
            border: 1px solid #DDDDDD;
            border-radius: 3px;
            padding: 9px 12px;
            font-size: 0.875rem;
            background-color: #FFFFFF;
            color: #131921;
            outline: none;
            transition: border-color 200ms, box-shadow 200ms;
        }
        .input-commerce:focus {
            border-color: #FF9900;
            box-shadow: 0 0 0 2px rgba(255, 153, 0, 0.35);
        }
    </style>
</head>
<body class="bg-retail-darkInk min-h-[100dvh] flex items-center justify-center p-4 antialiased text-slate-800">
    <div class="max-w-md w-full">
        <!-- Logo and Title -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-[6px] bg-retail-orange text-retail-darkInk shadow-lg mb-3">
                <i data-lucide="wallet-cards" class="w-7 h-7"></i>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-white tracking-tight">PT Technodrives Indonesia</h1>
            <p class="text-retail-cyan text-xs font-medium mt-1">Sistem Manajemen Cash Advance & Settlement</p>
        </div>

        <!-- Login Card (Design.md: rounded 6px, border 1px #DDDDDD, subtle shadow) -->
        <div class="bg-white rounded-[6px] shadow-2xl p-6 sm:p-7 border border-retail-surface">
            @if(session('info'))
                <div class="mb-4 bg-sky-50 border-l-4 border-retail-cyan p-3 rounded-[3px] text-xs text-sky-900 leading-relaxed">
                    {{ session('info') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-rose-50 border-l-4 border-rose-600 p-3 rounded-[3px] text-xs text-rose-900 leading-relaxed">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">Email Kantor</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </span>
                        <input type="email" name="email" value="{{ old('email', 'karyawan@technodrives.co.id') }}" required
                            class="w-full pl-9 input-commerce"
                            placeholder="nama@technodrives.co.id">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-retail-ink uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </span>
                        <input type="password" name="password" value="password" required
                            class="w-full pl-9 input-commerce"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-600 pt-1">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded-[2px] border-slate-300 text-retail-orange focus:ring-retail-orange">
                        <span>Ingat saya</span>
                    </label>
                    <span class="text-[11px] font-mono text-slate-400">(Default: password)</span>
                </div>

                <button type="submit" class="w-full btn-primary mt-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Masuk ke Sistem</span>
                </button>
            </form>

            <!-- Quick Demo Login Switcher -->
            <div class="mt-6 pt-5 border-t border-retail-surface">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center mb-2.5">
                    Akses Cepat Demo (1-Click Login):
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($demoUsers as $demo)
                        <form action="{{ route('quick-login') }}" method="POST">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $demo->id }}">
                            <button type="submit" class="w-full text-left p-2.5 rounded-[3px] border border-slate-200 hover:border-retail-orange hover:bg-slate-50 transition flex items-center space-x-2">
                                <span class="w-2.5 h-2.5 rounded-[2px] flex-shrink-0
                                    {{ $demo->role === 'direktur' ? 'bg-amber-500' : '' }}
                                    {{ $demo->role === 'finance' ? 'bg-purple-600' : '' }}
                                    {{ $demo->role === 'atasan' ? 'bg-blue-600' : '' }}
                                    {{ $demo->role === 'karyawan' ? 'bg-emerald-600' : '' }}
                                    {{ $demo->role === 'super_admin' ? 'bg-rose-600' : '' }}
                                "></span>
                                <div class="overflow-hidden">
                                    <span class="block text-xs font-bold text-retail-ink truncate">{{ $demo->name }}</span>
                                    <span class="block text-[10px] font-mono text-slate-500 uppercase font-semibold">{{ $demo->role }}</span>
                                </div>
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="text-center mt-5 text-[11px] font-mono text-slate-400">
            &copy; 2026 PT Technodrives Indonesia &bull; Design.md Specification
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>