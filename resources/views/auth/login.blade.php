<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Cash Advance & Settlement PT Technodrives</title>
    
    <!-- Google Fonts: Poppins (Headings/Buttons/Numbers) & Fira Sans (Body/Forms) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Fira Sans"', 'system-ui', 'sans-serif'],
                        heading: ['Poppins', 'sans-serif'],
                        mono: ['"Fira Sans"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            DEFAULT: '#FF3C00',
                            alt: '#FF3B00',
                            hover: '#FF3D00',
                        },
                        navy: {
                            DEFAULT: '#050A1E',
                            light: '#161A2B',
                        },
                        surface: '#F4F4F4',
                        bodygrey: '#7A7A7A',
                        cardborder: '#E1E1E1',
                        accentyellow: '#FFC704',
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Fira Sans', system-ui, sans-serif;
            background: linear-gradient(135deg, #050A1E 0%, #161A2B 65%, rgba(255, 60, 0, 0.22) 100%);
            min-height: 100vh;
        }
        .font-heading {
            font-family: 'Poppins', sans-serif;
        }
        .btn-brand {
            background-color: #FF3B00;
            color: #FFFFFF;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border-radius: 30px;
            padding: 13px 24px;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 14px rgba(255, 60, 0, 0.25);
        }
        .btn-brand:hover {
            background-color: #050A1E;
            color: #FFFFFF;
            border-color: rgba(255, 60, 0, 0.4);
            box-shadow: 0 6px 18px rgba(5, 10, 30, 0.35);
            transform: translateY(-1px);
        }
        .input-brand {
            height: 50px;
            border-radius: 15px;
            border: 1px solid rgba(122, 122, 122, 0.35);
            padding: 0 16px 0 44px;
            font-size: 14px;
            background-color: #FFFFFF;
            color: #050A1E;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-brand:focus {
            border-color: #FF3D00;
            box-shadow: 0 0 0 3px rgba(255, 61, 0, 0.18);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 antialiased selection:bg-brand selection:text-white">
    <div class="max-w-md w-full my-6">
        <!-- Logo and Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center p-3.5 bg-white rounded-2xl shadow-xl border border-cardborder mb-3">
                <img src="{{ asset('assets/images/logo.png') }}" alt="PT Technodrives Logo" class="h-9 w-auto object-contain">
            </div>
            <h1 class="text-xl sm:text-2xl font-heading font-bold text-white tracking-tight">PT Technodrives</h1>
            <p class="text-xs text-slate-300 mt-1 font-medium">Sistem Cash Advance & Settlement</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-[15px] shadow-2xl p-6 sm:p-8 border border-cardborder">
            <div class="mb-5 pb-3 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-heading font-bold text-navy">Masuk ke Akun</h2>
                    <p class="text-xs text-bodygrey mt-0.5">Silakan masuk dengan kredensial terdaftar.</p>
                </div>
                <div class="w-8 h-8 rounded-full bg-surface border border-cardborder flex items-center justify-center text-brand">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                </div>
            </div>

            @if(session('info'))
                <div class="mb-4 bg-orange-50 border-l-4 border-brand p-3 rounded-xl text-xs text-orange-950 leading-relaxed">
                    {{ session('info') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-rose-50 border-l-4 border-brand p-3 rounded-xl text-xs text-rose-900 leading-relaxed">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-navy uppercase tracking-wider mb-1.5 font-heading">Email Kantor</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-bodygrey pointer-events-none">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </span>
                        <input type="email" name="email" value="{{ old('email', 'karyawan@technodrives.co.id') }}" required
                            class="w-full input-brand"
                            placeholder="nama@technodrives.co.id">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-navy uppercase tracking-wider mb-1.5 font-heading">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-bodygrey pointer-events-none">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </span>
                        <input type="password" name="password" value="password" required
                            class="w-full input-brand"
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-bodygrey pt-1">
                    <label class="flex items-center space-x-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-brand focus:ring-brand border-slate-300">
                        <span class="text-navy">Ingat saya</span>
                    </label>
                    <span class="text-[11px] font-mono text-slate-400">(Default: password)</span>
                </div>

                <button type="submit" class="w-full btn-brand mt-2 cursor-pointer">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Masuk ke Sistem</span>
                </button>
            </form>

            <!-- Quick Demo Login Switcher -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-[11px] font-heading font-semibold text-bodygrey uppercase tracking-wider text-center mb-3">
                    Akses Cepat Demo (1-Click Switch):
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($demoUsers as $demo)
                        <form action="{{ route('quick-login') }}" method="POST">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $demo->id }}">
                            <button type="submit" class="w-full text-left p-2.5 rounded-[12px] border border-cardborder bg-surface/50 hover:bg-white hover:border-brand hover:shadow-sm transition flex items-center space-x-2.5 group cursor-pointer">
                                <span class="w-2.5 h-2.5 rounded-full flex-shrink-0
                                    {{ $demo->role === 'direktur' ? 'bg-[#FFC704]' : '' }}
                                    {{ $demo->role === 'finance' ? 'bg-[#FF3C00]' : '' }}
                                    {{ $demo->role === 'atasan' ? 'bg-[#050A1E]' : '' }}
                                    {{ $demo->role === 'karyawan' ? 'bg-[#161A2B]' : '' }}
                                    {{ $demo->role === 'super_admin' ? 'bg-[#FF3B00]' : '' }}
                                "></span>
                                <div class="overflow-hidden">
                                    <span class="block text-xs font-semibold text-navy group-hover:text-brand transition truncate">{{ $demo->name }}</span>
                                    <span class="block text-[10px] font-mono text-bodygrey uppercase">{{ $demo->role }}</span>
                                </div>
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="text-center mt-5 text-[11px] text-slate-400 font-normal">
            &copy; 2026 PT Technodrives Indonesia &bull; Expert Electrical Solutions & Testing
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
