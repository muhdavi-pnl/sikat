<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>{{ config('app.name', 'SIKAT') }} &mdash; Sistem Informasi Kepegawaian Terintegrasi PNL</title>
    <meta name="description" content="Sistem Informasi Kepegawaian Terintegrasi (SIKAT) Politeknik Negeri Lhokseumawe. Layanan mandiri data kepegawaian, arsip digital, dan usulan online."/>
    <meta name="keywords" content="SIKAT, Kepegawaian, Politeknik Negeri Lhokseumawe, PNL, Layanan Pegawai, Cuti Online, Arsip Digital, Grafik Pegawai"/>
    <meta name="author" content="Bagian Kepegawaian PNL"/>

    <!-- Favicons -->
    <link rel="icon" type="image/png" href="{{ asset('img/logo-pnl.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#0f172a',
                        },
                        brand: {
                            navy: '#0b132b',
                            blue: '#1c3d5a',
                            accent: '#38bdf8',
                            teal: '#0d9488',
                        }
                    },
                    boxShadow: {
                        'glow': '0 0 50px -10px rgba(37, 99, 235, 0.25)',
                        'card': '0 10px 30px -5px rgba(15, 23, 42, 0.06), 0 4px 6px -2px rgba(15, 23, 42, 0.02)',
                        'elevated': '0 20px 40px -15px rgba(15, 23, 42, 0.12)',
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.13.3/dist/cdn.min.js"></script>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            overflow-x: hidden;
        }

        .gradient-brand {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #2563eb 100%);
        }

        .gradient-accent {
            background: linear-gradient(135deg, #2563eb 0%, #38bdf8 100%);
        }

        .gradient-surface {
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6);
        }

        .glass-dark {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .mesh-bg {
            background-image: 
                radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.12) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(56, 189, 248, 0.1) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(99, 102, 241, 0.08) 0px, transparent 50%);
        }

        .nav-section-link {
            position: relative;
            transition: color 0.2s ease;
        }

        .nav-section-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0%;
            height: 2px;
            background: #2563eb;
            border-radius: 2px;
            transition: width 0.25s ease;
        }

        .nav-section-link:hover::after,
        .nav-section-link-active::after {
            width: 100%;
        }

        .nav-section-link-active {
            color: #2563eb !important;
            font-weight: 700;
        }

        /* Floating Badge Animation */
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        .animate-float {
            animation: floatSlow 4s ease-in-out infinite;
        }

        .animate-float-delayed {
            animation: floatSlow 4s ease-in-out 2s infinite;
        }
    </style>
</head>
<body class="antialiased text-slate-800 bg-slate-50 selection:bg-primary-500 selection:text-white" x-data="{ mobileMenuOpen: false }">

    <!-- NAVBAR -->
    <header id="header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 py-4 bg-white/80 backdrop-blur-md border-b border-slate-200/60 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <!-- Brand / Logo -->
                <a href="{{ route('landing') }}" class="flex items-center gap-3 group focus:outline-none">
                    <img src="{{ asset('img/logo-pnl.png') }}" class="h-10 sm:h-11 w-auto object-contain transition-transform duration-300 group-hover:scale-105" alt="Logo Politeknik Negeri Lhokseumawe" />
                    <div class="flex flex-col">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xl font-extrabold tracking-tight text-slate-900 group-hover:text-primary-600 transition-colors">SIKAT</span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-primary-100 text-primary-700">PNL</span>
                        </div>
                        <span class="text-[11px] font-medium text-slate-500 tracking-wide hidden sm:block">Sistem Informasi Kepegawaian Terintegrasi</span>
                    </div>
                </a>

                <!-- Desktop Navigation Links -->
                <nav class="hidden lg:flex items-center gap-1 xl:gap-2">
                    <a href="{{ route('landing') }}" class="px-3.5 py-2 text-sm font-semibold text-primary-600 hover:text-primary-700 rounded-lg transition-colors">
                        Beranda
                    </a>
                    <a class="nav-section-link px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-primary-600 rounded-lg transition-colors"
                       data-section-link
                       href="#sikat">Tentang SIKAT</a>
                    <a class="nav-section-link px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-primary-600 rounded-lg transition-colors"
                       data-section-link
                       href="#layanan">Layanan</a>
                    <a class="nav-section-link px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-primary-600 rounded-lg transition-colors"
                       data-section-link
                       href="#statistik">Statistik Pegawai</a>
                    <a class="nav-section-link px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-primary-600 rounded-lg transition-colors"
                       data-section-link
                       href="#faq">FAQ</a>
                    <a class="nav-section-link px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-primary-600 rounded-lg transition-colors"
                       data-section-link
                       href="#testimoni">Testimoni</a>
                    <a class="nav-section-link px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-primary-600 rounded-lg transition-colors"
                       data-section-link
                       href="#kontak">Kontak</a>
                </nav>

                <!-- Desktop Auth Actions -->
                <div class="hidden lg:flex items-center gap-3">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}"
                               id="navAction"
                               data-nav-action
                               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5">
                                <i class="fa-solid fa-gauge-high text-xs"></i>
                                <span>Dashboard</span>
                            </a>
                        @else
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                   id="navActionRegister"
                                   data-nav-action
                                   class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-slate-700 hover:text-primary-600 hover:bg-slate-100/80 rounded-xl transition-all duration-200">
                                    Register
                                </a>
                            @endif

                            <a href="{{ route('login') }}"
                               id="navActionLogin"
                               data-nav-action
                               class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow-md shadow-primary-500/20 hover:shadow-primary-500/30 transition-all duration-200 transform hover:-translate-y-0.5">
                                <i class="fa-solid fa-right-to-bracket text-xs"></i>
                                <span>Login</span>
                            </a>
                        @endauth
                    @endif
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex lg:hidden items-center">
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                            type="button"
                            class="inline-flex items-center justify-center p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
                            aria-label="Toggle navigation">
                        <i :class="mobileMenuOpen ? 'fa-solid fa-xmark text-xl' : 'fa-solid fa-bars text-xl'"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Drawer -->
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-3"
             class="lg:hidden bg-white border-b border-slate-200 px-4 pt-3 pb-6 shadow-xl"
             @click.away="mobileMenuOpen = false"
             style="display: none;">
            <div class="flex flex-col space-y-2 pt-2">
                <a href="{{ route('landing') }}" @click="mobileMenuOpen = false" class="px-4 py-2.5 text-sm font-semibold text-primary-600 bg-primary-50 rounded-lg">
                    <i class="fa-solid fa-house mr-2 w-5 text-center"></i> Beranda
                </a>
                <a class="px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-primary-600 rounded-lg"
                   @click="mobileMenuOpen = false"
                   href="#sikat">
                    <i class="fa-solid fa-circle-info mr-2 w-5 text-center text-slate-400"></i> Tentang SIKAT
                </a>
                <a href="#layanan" @click="mobileMenuOpen = false" class="px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-primary-600 rounded-lg">
                    <i class="fa-solid fa-layer-group mr-2 w-5 text-center text-slate-400"></i> Layanan Unggulan
                </a>
                <a href="#statistik" @click="mobileMenuOpen = false" class="px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-primary-600 rounded-lg">
                    <i class="fa-solid fa-chart-pie mr-2 w-5 text-center text-slate-400"></i> Statistik Pegawai
                </a>
                <a class="px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-primary-600 rounded-lg"
                   @click="mobileMenuOpen = false"
                   href="#faq">
                    <i class="fa-solid fa-circle-question mr-2 w-5 text-center text-slate-400"></i> FAQ
                </a>
                <a class="px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-primary-600 rounded-lg"
                   @click="mobileMenuOpen = false"
                   href="#testimoni">
                    <i class="fa-solid fa-comment-dots mr-2 w-5 text-center text-slate-400"></i> Testimoni
                </a>
                <a class="px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-primary-600 rounded-lg"
                   @click="mobileMenuOpen = false"
                   href="#kontak">
                    <i class="fa-solid fa-headset mr-2 w-5 text-center text-slate-400"></i> Kontak & Bantuan
                </a>
            </div>

            <div class="pt-4 mt-3 border-t border-slate-100 flex flex-col gap-2">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="w-full text-center py-2.5 px-4 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow">
                            Buka Dashboard
                        </a>
                    @else
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="w-full text-center py-2 px-4 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl">
                                Register Akun Baru
                            </a>
                        @endif

                        <a href="{{ route('login') }}"
                           class="w-full text-center py-2.5 px-4 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow">
                            Login Pegawai
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="relative pt-32 pb-20 md:pt-40 md:pb-28 overflow-hidden mesh-bg">
        <!-- Ambient Decorative Circles -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-primary-400/10 rounded-full blur-3xl pointer-events-none -z-10"></div>
        <div class="absolute top-1/3 right-10 w-[400px] h-[400px] bg-sky-400/10 rounded-full blur-3xl pointer-events-none -z-10"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                <!-- Left Column (Content) -->
                <div class="lg:col-span-7 text-center lg:text-left">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-primary-50 border border-primary-200/60 shadow-sm mb-6">
                        <span class="flex h-2 w-2 rounded-full bg-primary-600 animate-pulse"></span>
                        <span class="text-xs font-bold text-primary-700 tracking-wide uppercase">Layanan Kepegawaian Terpadu PNL</span>
                    </div>

                    <!-- Headline -->
                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-[2.85rem] font-extrabold text-slate-900 tracking-tight leading-[1.15] mb-6">
                        Pengelolaan Data & Layanan Kepegawaian Lebih <span class="bg-gradient-to-r from-primary-600 to-sky-500 bg-clip-text text-transparent">Cepat, Cerdas & Terintegrasi</span>
                    </h1>

                    <!-- Description -->
                    <p class="text-base sm:text-lg text-slate-600 leading-relaxed mb-8 max-w-2xl mx-auto lg:mx-0">
                        SIKAT hadir untuk seluruh Dosen dan Tenaga Kependidikan Politeknik Negeri Lhokseumawe. Kelola arsip digital, ajukan cuti, monitoring usulan kepegawaian, dan pantau peta jabatan secara mandiri, akuntabel, dan transparan.
                    </p>

                    <!-- CTAs -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 mb-10">
                        @auth
                            <a href="{{ url('/dashboard') }}"
                               class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-3.5 text-base font-bold text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow-lg shadow-primary-500/25 hover:shadow-primary-500/35 transition-all duration-200 transform hover:-translate-y-0.5">
                                <i class="fa-solid fa-gauge-high"></i>
                                <span>Masuk ke Dashboard</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-3.5 text-base font-bold text-white bg-primary-600 hover:bg-primary-700 rounded-xl shadow-lg shadow-primary-500/25 hover:shadow-primary-500/35 transition-all duration-200 transform hover:-translate-y-0.5">
                                <i class="fa-solid fa-right-to-bracket"></i>
                                <span>Masuk ke Sistem</span>
                            </a>
                            <a href="#sikat"
                               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 text-base font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300/80 rounded-xl shadow-sm hover:shadow transition-all duration-200">
                                <i class="fa-regular fa-circle-play text-primary-600"></i>
                                <span>Pelajari Fitur</span>
                            </a>
                        @endauth
                    </div>

                    <!-- Highlight Badges -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-6 border-t border-slate-200/80 text-left">
                        <div class="flex flex-col">
                            <span class="text-2xl font-extrabold text-slate-900">{{ $genderChart['total'] > 0 ? $genderChart['total'].'+' : '500+' }}</span>
                            <span class="text-xs font-medium text-slate-500">Pegawai & Dosen</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-2xl font-extrabold text-primary-600">100%</span>
                            <span class="text-xs font-medium text-slate-500">Digital & Mandiri</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-2xl font-extrabold text-slate-900">24/7</span>
                            <span class="text-xs font-medium text-slate-500">Akses Kapanpun</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-2xl font-extrabold text-emerald-600">Akurat</span>
                            <span class="text-xs font-medium text-slate-500">Arsip Terdata</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column (Modern Interactive Visual Mockup) -->
                <div class="lg:col-span-5 relative">
                    <div class="relative mx-auto max-w-md lg:max-w-none">
                        
                        <!-- Main Visual Card -->
                        <div class="relative bg-white rounded-3xl p-6 sm:p-7 shadow-elevated border border-slate-100 overflow-hidden">
                            <!-- Window Header -->
                            <div class="flex items-center justify-between pb-5 border-b border-slate-100 mb-5">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-rose-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                                </div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Portal SIKAT PNL</span>
                                <div class="h-4 w-4 text-slate-300">
                                    <i class="fa-solid fa-shield-halved text-xs text-primary-500"></i>
                                </div>
                            </div>

                            <!-- User Greeting Widget -->
                            <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-4 text-white mb-4 shadow-sm">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-primary-500/20 border border-primary-400/40 flex items-center justify-center text-primary-300 font-bold text-sm">
                                            <i class="fa-solid fa-user-tie"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-bold text-white">Selamat Datang di SIKAT</h4>
                                            <p class="text-[11px] text-slate-300">Kepegawaian Politeknik Negeri Lhokseumawe</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        Aktif
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-700/60 text-xs">
                                    <div>
                                        <span class="text-slate-400 text-[10px] block">Layanan Terbuka</span>
                                        <span class="font-semibold text-white">Cuti, Pangkat, Arsip</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 text-[10px] block">Status Sistem</span>
                                        <span class="font-semibold text-emerald-400">Online 24 Jam</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Sample Quick Action Cards -->
                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 hover:border-primary-200 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xs">
                                            <i class="fa-solid fa-calendar-check"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800">Usulan Cuti Pegawai</div>
                                            <div class="text-[10px] text-slate-500">Tahunan, Besar, Melahirkan, Alasan Penting</div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold text-primary-600 bg-primary-50 px-2 py-0.5 rounded-md">Proses Cepat</span>
                                </div>

                                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 hover:border-primary-200 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs">
                                            <i class="fa-solid fa-box-archive"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800">Manajemen Arsip & SK</div>
                                            <div class="text-[10px] text-slate-500">Penyimpanan fisik rak/lemari & digital terpadu</div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">Tersinkron</span>
                                </div>

                                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 hover:border-primary-200 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">
                                            <i class="fa-solid fa-sitemap"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-800">Peta Jabatan & Formasi</div>
                                            <div class="text-[10px] text-slate-500">Struktur organisasi dan karir pegawai</div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">Transparan</span>
                                </div>
                            </div>
                        </div>

                        <!-- Floating Notification Badge 1 (Top Right) -->
                        <div class="absolute -top-5 -right-4 sm:-right-6 bg-white p-3.5 rounded-2xl shadow-xl border border-slate-100 flex items-center gap-3 animate-float hidden sm:flex">
                            <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center shadow-md shadow-emerald-500/30">
                                <i class="fa-solid fa-check text-sm"></i>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Usulan Cuti Disetujui</div>
                                <div class="text-[10px] text-slate-500">PYBMC telah memvalidasi</div>
                            </div>
                        </div>

                        <!-- Floating Notification Badge 2 (Bottom Left) -->
                        <div class="absolute -bottom-5 -left-4 sm:-left-6 bg-white p-3.5 rounded-2xl shadow-xl border border-slate-100 flex items-center gap-3 animate-float-delayed hidden sm:flex">
                            <div class="w-9 h-9 rounded-xl bg-primary-600 text-white flex items-center justify-center shadow-md shadow-primary-500/30">
                                <i class="fa-solid fa-folder-open text-sm"></i>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-900">Arsip Digital Terverifikasi</div>
                                <div class="text-[10px] text-slate-500">Dokumen SK tersimpan aman</div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- SECTION: TENTANG SIKAT (id="sikat") -->
    <section id="sikat" class="py-20 md:py-28 bg-white border-y border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-bold uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-building-columns text-[11px]"></i> Profil & Gambaran Umum
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Tentang SIKAT Politeknik Negeri Lhokseumawe
                </h2>
                <div class="w-20 h-1 bg-primary-600 mx-auto mt-4 rounded-full"></div>
                <p class="mt-4 text-base sm:text-lg text-slate-600">
                    Platform kepegawaian modern yang dirancang untuk mengintegrasikan administrasi sumber daya manusia di lingkungan Politeknik Negeri Lhokseumawe.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                
                <!-- Left: Info Card & Explanations -->
                <div class="space-y-6">
                    <div class="bg-slate-50 rounded-2xl p-6 sm:p-8 border border-slate-100">
                        <h3 class="text-xl font-bold text-slate-900 mb-3 flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-lg bg-primary-600 text-white flex items-center justify-center text-sm">
                                <i class="fa-solid fa-lightbulb"></i>
                            </span>
                            <span>Apa itu SIKAT?</span>
                        </h3>
                        <p class="text-slate-600 leading-relaxed text-sm sm:text-base">
                            <strong class="text-slate-900">SIKAT</strong> adalah singkatan dari <strong class="text-slate-900">Sistem Informasi Kepegawaian Terintegrasi</strong>, sebuah sistem komprehensif yang dibangun khusus untuk mengelola data kepegawaian, arsip dokumen, serta administrasi layanan pegawai di Politeknik Negeri Lhokseumawe secara terpadu dan paperless.
                        </p>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-6 sm:p-8 border border-slate-100">
                        <h3 class="text-xl font-bold text-slate-900 mb-3 flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-lg bg-sky-600 text-white flex items-center justify-center text-sm">
                                <i class="fa-solid fa-bullseye"></i>
                            </span>
                            <span>Tujuan & Manfaat</span>
                        </h3>
                        <p class="text-slate-600 leading-relaxed text-sm sm:text-base mb-4">
                            Sistem ini memfasilitasi setiap Pegawai, Dosen, Tenaga Kependidikan, serta Pimpinan untuk mengakses data pribadi, melacak berkas kepegawaian, dan mengajukan layanan kapan saja dan di mana saja.
                        </p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <div class="flex items-center gap-2 text-sm text-slate-700 font-medium">
                                <i class="fa-solid fa-circle-check text-primary-600 text-sm"></i>
                                <span>Efisiensi Waktu & Biaya</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-slate-700 font-medium">
                                <i class="fa-solid fa-circle-check text-primary-600 text-sm"></i>
                                <span>Paperless & Ramah Lingkungan</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-slate-700 font-medium">
                                <i class="fa-solid fa-circle-check text-primary-600 text-sm"></i>
                                <span>Transparansi Usulan</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-slate-700 font-medium">
                                <i class="fa-solid fa-circle-check text-primary-600 text-sm"></i>
                                <span>Keamanan Data Terstandar</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Pillar Features Visual Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50/50 p-6 rounded-2xl border border-blue-100 hover:shadow-md transition-all">
                        <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xl mb-4 shadow-sm">
                            <i class="fa-solid fa-laptop-file"></i>
                        </div>
                        <h4 class="text-base font-bold text-slate-900 mb-2">Layanan Mandiri Pegawai</h4>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Pegawai dapat langsung memperbarui profil pribadi, mengunggah ijazah/SK, dan mengajukan permohonan dinas secara mandiri.
                        </p>
                    </div>

                    <div class="bg-gradient-to-br from-sky-50 to-blue-50/50 p-6 rounded-2xl border border-sky-100 hover:shadow-md transition-all">
                        <div class="w-12 h-12 rounded-xl bg-sky-600 text-white flex items-center justify-center text-xl mb-4 shadow-sm">
                            <i class="fa-solid fa-folder-tree"></i>
                        </div>
                        <h4 class="text-base font-bold text-slate-900 mb-2">Arsip Fisik & Digital</h4>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Integrasi cerdas lokasi arsip fisik (Gedung, Ruang, Lemari, Rak) dengan file digital scan untuk memudahkan pencarian berkas.
                        </p>
                    </div>

                    <div class="bg-gradient-to-br from-emerald-50 to-teal-50/50 p-6 rounded-2xl border border-emerald-100 hover:shadow-md transition-all">
                        <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xl mb-4 shadow-sm">
                            <i class="fa-solid fa-diagram-project"></i>
                        </div>
                        <h4 class="text-base font-bold text-slate-900 mb-2">Struktur Peta Jabatan</h4>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Visualisasi hierarki jabatan, kuota formasi, dan jenjang karir pegawai di lingkungan institusi Politeknik Negeri Lhokseumawe.
                        </p>
                    </div>

                    <div class="bg-gradient-to-br from-amber-50 to-orange-50/50 p-6 rounded-2xl border border-amber-100 hover:shadow-md transition-all">
                        <div class="w-12 h-12 rounded-xl bg-amber-600 text-white flex items-center justify-center text-xl mb-4 shadow-sm">
                            <i class="fa-solid fa-stamp"></i>
                        </div>
                        <h4 class="text-base font-bold text-slate-900 mb-2">Approval Berjenjang</h4>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                            Persetujuan usulan bertingkat dari Atasan Langsung, Bagian Kepegawaian, hingga Pejabat Yang Berwenang (PYBMC).
                        </p>
                    </div>

                </div>

            </div>

        </div>
    </section>

    <!-- SECTION: FITUR & LAYANAN UNGGULAN (id="layanan") -->
    <section id="layanan" class="py-20 md:py-28 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-bold uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-wand-magic-sparkles text-[11px]"></i> Modul & Fitur Utama
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Fitur Lengkap untuk Kebutuhan Kepegawaian
                </h2>
                <div class="w-20 h-1 bg-primary-600 mx-auto mt-4 rounded-full"></div>
                <p class="mt-4 text-base sm:text-lg text-slate-600">
                    Nikmati kemudahan seluruh proses administrasi tanpa antrean fisik dan dokumen bertumpuk.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- Card 1 -->
                <div class="bg-white rounded-2xl p-7 border border-slate-200/80 shadow-card hover:shadow-elevated transition-all duration-300 hover:-translate-y-1 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-blue-100 text-primary-600 flex items-center justify-center text-xl mb-5">
                            <i class="fa-solid fa-umbrella-beach"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Pengajuan Cuti Online</h3>
                        <p class="text-sm text-slate-600 leading-relaxed mb-4">
                            Pengajuan berbagai jenis cuti (tahunan, melahirkan, sakit, alasan penting) secara digital lengkap dengan cetak formulir resmi dan riwayat sisa cuti.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-primary-600">
                        <span>Cuti Mandiri</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-2xl p-7 border border-slate-200/80 shadow-card hover:shadow-elevated transition-all duration-300 hover:-translate-y-1 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl mb-5">
                            <i class="fa-solid fa-box-archive"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">E-Arsip Dokumen Pegawai</h3>
                        <p class="text-sm text-slate-600 leading-relaxed mb-4">
                            Digitalisasi berkas SK, Ijazah, Transkrip, Sertifikasi, dan Dokumen Kepegawaian dengan pencatatan lokasi fisik rak & lemari penyimpanan.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-indigo-600">
                        <span>Penyimpanan Aman</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-2xl p-7 border border-slate-200/80 shadow-card hover:shadow-elevated transition-all duration-300 hover:-translate-y-1 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-xl mb-5">
                            <i class="fa-solid fa-award"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Layanan Usulan & Fungsional</h3>
                        <p class="text-sm text-slate-600 leading-relaxed mb-4">
                            Usulan kenaikan pangkat, jabatan fungsional dosen/tendik, dan layanan kepegawaian lainnya dengan sistem verifikasi syarat otomatis.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-sky-600">
                        <span>Monitoring Real-time</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="bg-white rounded-2xl p-7 border border-slate-200/80 shadow-card hover:shadow-elevated transition-all duration-300 hover:-translate-y-1 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl mb-5">
                            <i class="fa-solid fa-sitemap"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Peta Jabatan & Analisis</h3>
                        <p class="text-sm text-slate-600 leading-relaxed mb-4">
                            Dashboard pemetaan formasi, jenjang karier, dan struktur jabatan institusi yang informatif untuk pimpinan dan pengelola kepegawaian.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-emerald-600">
                        <span>Data Terstruktur</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </div>

                <!-- Card 5 -->
                <div class="bg-white rounded-2xl p-7 border border-slate-200/80 shadow-card hover:shadow-elevated transition-all duration-300 hover:-translate-y-1 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-xl mb-5">
                            <i class="fa-solid fa-signature"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Validasi & Approval PYBMC</h3>
                        <p class="text-sm text-slate-600 leading-relaxed mb-4">
                            Siklus persetujuan bertingkat dengan catatan verifikasi transparan dari Atasan Langsung hingga Pejabat Yang Berwenang Menetapkan Cuti.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-purple-600">
                        <span>Audit Log Jelas</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </div>

                <!-- Card 6 -->
                <div class="bg-white rounded-2xl p-7 border border-slate-200/80 shadow-card hover:shadow-elevated transition-all duration-300 hover:-translate-y-1 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl mb-5">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Akses Responsif Multi-Device</h3>
                        <p class="text-sm text-slate-600 leading-relaxed mb-4">
                            Dirancang dengan teknologi modern yang dapat diakses dengan mulus melalui perangkat smartphone, tablet, maupun komputer desktop.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-amber-600">
                        <span>Fleksibel & Cepat</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- SECTION: STATISTIK & GRAFIK (id="statistik") -->
    <section id="statistik" class="py-20 md:py-28 bg-white border-t border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-bold uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-chart-column text-[11px]"></i> Data & Demografi Kepegawaian
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Statistik & Grafik Pegawai PNL
                </h2>
                <div class="w-20 h-1 bg-primary-600 mx-auto mt-4 rounded-full"></div>
                <p class="mt-4 text-base sm:text-lg text-slate-600">
                    Visualisasi komposisi sumber daya manusia di Politeknik Negeri Lhokseumawe secara transparan dan terstruktur.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Grafik 1: Jenis Kelamin -->
                <div class="bg-slate-50 rounded-2xl p-6 sm:p-7 border border-slate-200/80 shadow-card flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-lg">
                                    <i class="fa-solid fa-venus-mars"></i>
                                </div>
                                <div>
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900">Grafik Jumlah Pegawai per Jenis Kelamin</h3>
                                    <p class="text-xs text-slate-500">Distribusi gender pegawai dan dosen</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                Total: {{ $genderChart['total'] }} Pegawai
                            </span>
                        </div>

                        <div class="relative h-64 sm:h-72 w-full my-2">
                            <canvas id="chartGender"></canvas>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-4 border-t border-slate-200/70 mt-4 text-center">
                        <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-xs">
                            <div class="text-xs font-semibold text-slate-500 flex items-center justify-center gap-1.5 mb-1">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-600 inline-block"></span>
                                <span>Laki-laki</span>
                            </div>
                            <div class="text-lg font-extrabold text-slate-900">{{ $genderChart['data'][0] ?? 0 }}</div>
                            <div class="text-[11px] text-slate-400 font-medium">
                                {{ $genderChart['total'] > 0 ? round((($genderChart['data'][0] ?? 0) / $genderChart['total']) * 100, 1) : 0 }}%
                            </div>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-slate-100 shadow-xs">
                            <div class="text-xs font-semibold text-slate-500 flex items-center justify-center gap-1.5 mb-1">
                                <span class="w-2.5 h-2.5 rounded-full bg-pink-500 inline-block"></span>
                                <span>Perempuan</span>
                            </div>
                            <div class="text-lg font-extrabold text-slate-900">{{ $genderChart['data'][1] ?? 0 }}</div>
                            <div class="text-[11px] text-slate-400 font-medium">
                                {{ $genderChart['total'] > 0 ? round((($genderChart['data'][1] ?? 0) / $genderChart['total']) * 100, 1) : 0 }}%
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grafik 2: Golongan -->
                <div class="bg-slate-50 rounded-2xl p-6 sm:p-7 border border-slate-200/80 shadow-card flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg">
                                    <i class="fa-solid fa-layer-group"></i>
                                </div>
                                <div>
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900">Grafik Jumlah Pegawai per Golongan</h3>
                                    <p class="text-xs text-slate-500">Kepangkatan & golongan ruang ASN</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                Total: {{ $golonganChart['total'] }} Pegawai
                            </span>
                        </div>

                        <div class="relative h-64 sm:h-72 w-full my-2">
                            <canvas id="chartGolongan"></canvas>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-200/70 mt-4 flex items-center justify-between text-xs text-slate-500">
                        <span><i class="fa-solid fa-circle-info mr-1 text-primary-500"></i> Tersebar di {{ count($golonganChart['labels']) }} Golongan Ruang</span>
                        <span class="font-semibold text-slate-700">Golongan Terbanyak: {{ $golonganChart['terbanyak'] ?? '-' }} ({{ $golonganChart['terbanyak_total'] ?? 0 }} Pegawai)</span>
                    </div>
                </div>

                <!-- Grafik 3: Tingkat Pendidikan -->
                <div class="bg-slate-50 rounded-2xl p-6 sm:p-7 border border-slate-200/80 shadow-card flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                </div>
                                <div>
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900">Grafik Jumlah Pegawai per Tingkat Pendidikan</h3>
                                    <p class="text-xs text-slate-500">Jenjang pendidikan formal terakhir</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Total: {{ $pendidikanChart['total'] }} Pegawai
                            </span>
                        </div>

                        <div class="relative h-64 sm:h-72 w-full my-2">
                            <canvas id="chartPendidikan"></canvas>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-200/70 mt-4 flex items-center justify-between text-xs text-slate-500">
                        <span><i class="fa-solid fa-circle-check mr-1 text-emerald-500"></i> Kualifikasi Dosen & Tendik</span>
                        <span class="font-semibold text-slate-700">{{ count($pendidikanChart['labels']) }} Kategori Jenjang</span>
                    </div>
                </div>

                <!-- Grafik 4: Eselon Jabatan -->
                <div class="bg-slate-50 rounded-2xl p-6 sm:p-7 border border-slate-200/80 shadow-card flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg">
                                    <i class="fa-solid fa-ranking-star"></i>
                                </div>
                                <div>
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900">Grafik Jumlah Pegawai per Eselon Jabatan</h3>
                                    <p class="text-xs text-slate-500">Tingkat eselonisasi struktural & fungsional</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                Total: {{ $eselonChart['total'] }} Pegawai
                            </span>
                        </div>

                        <div class="relative h-64 sm:h-72 w-full my-2">
                            <canvas id="chartEselon"></canvas>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-200/70 mt-4 flex items-center justify-between text-xs text-slate-500">
                        <span><i class="fa-solid fa-shield-halved mr-1 text-amber-500"></i> Jabatan Struktural & Non-Eselon</span>
                        <span class="font-semibold text-slate-700">{{ count($eselonChart['labels']) }} Klasifikasi Eselon</span>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- SECTION: FAQ (id="faq") -->
    <section id="faq" class="py-20 md:py-28 bg-white border-t border-slate-200/80">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-2xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-bold uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-circle-question text-[11px]"></i> Pusat Tanya Jawab
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Frequently Asked Questions
                </h2>
                <div class="w-20 h-1 bg-primary-600 mx-auto mt-4 rounded-full"></div>
                <p class="mt-4 text-base sm:text-lg text-slate-600">
                    Pertanyaan yang sering diajukan mengenai penggunaan sistem SIKAT.
                </p>
            </div>

            <!-- Modern Alpine Accordion List -->
            <div class="space-y-4" x-data="{ activeTab: 1 }">
                
                <!-- FAQ Item 1 -->
                <div class="bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden transition-all duration-200"
                     :class="activeTab === 1 ? 'ring-2 ring-primary-500/20 bg-white border-primary-300 shadow-sm' : ''">
                    <button @click="activeTab = activeTab === 1 ? 0 : 1"
                            class="w-full flex items-center justify-between p-5 sm:p-6 text-left focus:outline-none">
                        <span class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold shrink-0">1</span>
                            <span>Mengapa SIKAT?</span>
                        </span>
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 transition-transform duration-300"
                             :class="activeTab === 1 ? 'rotate-180 bg-primary-50 text-primary-600' : ''">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </button>
                    <div x-show="activeTab === 1"
                         x-collapse
                         class="px-5 sm:px-6 pb-6 pt-0 text-slate-600 text-sm sm:text-base leading-relaxed border-t border-slate-100">
                        <p class="pt-4">
                            SIKAT (Sistem Informasi Kepegawaian Terintegrasi) hadir untuk memudahkan pengelolaan data kepegawaian, arsip digital, dan layanan kepegawaian di Politeknik Negeri Lhokseumawe dalam satu platform terintegrasi.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden transition-all duration-200"
                     :class="activeTab === 2 ? 'ring-2 ring-primary-500/20 bg-white border-primary-300 shadow-sm' : ''">
                    <button @click="activeTab = activeTab === 2 ? 0 : 2"
                            class="w-full flex items-center justify-between p-5 sm:p-6 text-left focus:outline-none">
                        <span class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold shrink-0">2</span>
                            <span>Apakah dikenakan biaya?</span>
                        </span>
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 transition-transform duration-300"
                             :class="activeTab === 2 ? 'rotate-180 bg-primary-50 text-primary-600' : ''">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </button>
                    <div x-show="activeTab === 2"
                         x-collapse
                         class="px-5 sm:px-6 pb-6 pt-0 text-slate-600 text-sm sm:text-base leading-relaxed border-t border-slate-100">
                        <p class="pt-4">
                            Semua fitur yang ada di sistem ini <i>free</i> alias tanpa dipungut biaya bagi seluruh civitas akademika dan pegawai Politeknik Negeri Lhokseumawe.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden transition-all duration-200"
                     :class="activeTab === 3 ? 'ring-2 ring-primary-500/20 bg-white border-primary-300 shadow-sm' : ''">
                    <button @click="activeTab = activeTab === 3 ? 0 : 3"
                            class="w-full flex items-center justify-between p-5 sm:p-6 text-left focus:outline-none">
                        <span class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold shrink-0">3</span>
                            <span>Siapa saja yang bisa menggunakan SIKAT?</span>
                        </span>
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 transition-transform duration-300"
                             :class="activeTab === 3 ? 'rotate-180 bg-primary-50 text-primary-600' : ''">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </button>
                    <div x-show="activeTab === 3"
                         x-collapse
                         class="px-5 sm:px-6 pb-6 pt-0 text-slate-600 text-sm sm:text-base leading-relaxed border-t border-slate-100">
                        <p class="pt-4">
                            Semua Pegawai di Politeknik Negeri Lhokseumawe (PNL) baik Dosen, Tenaga Kependidikan, maupun Pengelola Kepegawaian dan Pimpinan bisa menggunakan sistem ini sesuai hak akses masing-masing.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden transition-all duration-200"
                     :class="activeTab === 4 ? 'ring-2 ring-primary-500/20 bg-white border-primary-300 shadow-sm' : ''">
                    <button @click="activeTab = activeTab === 4 ? 0 : 4"
                            class="w-full flex items-center justify-between p-5 sm:p-6 text-left focus:outline-none">
                        <span class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold shrink-0">4</span>
                            <span>Bagaimana prosedur penggunaan sistem ini?</span>
                        </span>
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 transition-transform duration-300"
                             :class="activeTab === 4 ? 'rotate-180 bg-primary-50 text-primary-600' : ''">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </button>
                    <div x-show="activeTab === 4"
                         x-collapse
                         class="px-5 sm:px-6 pb-6 pt-0 text-slate-600 text-sm sm:text-base leading-relaxed border-t border-slate-100">
                        <p class="pt-4">
                            Setiap Pegawai dapat melapor ke bagian Kepegawaian PNL untuk dibuatkan akun. Setelah dibuatkan akun, harap langsung mengubah <i>password</i> pada saat login pertama untuk alasan keamanan.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="bg-slate-50 border border-slate-200 rounded-2xl overflow-hidden transition-all duration-200"
                     :class="activeTab === 5 ? 'ring-2 ring-primary-500/20 bg-white border-primary-300 shadow-sm' : ''">
                    <button @click="activeTab = activeTab === 5 ? 0 : 5"
                            class="w-full flex items-center justify-between p-5 sm:p-6 text-left focus:outline-none">
                        <span class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center text-xs font-bold shrink-0">5</span>
                            <span>Jika mengalami kendala kemana kami harus melapor?</span>
                        </span>
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 transition-transform duration-300"
                             :class="activeTab === 5 ? 'rotate-180 bg-primary-50 text-primary-600' : ''">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </button>
                    <div x-show="activeTab === 5"
                         x-collapse
                         class="px-5 sm:px-6 pb-6 pt-0 text-slate-600 text-sm sm:text-base leading-relaxed border-t border-slate-100">
                        <p class="pt-4">
                            Silahkan hubungi Admin SIKAT melalui kontak yang ada di website ini atau melalui saluran WhatsApp resmi bagian Kepegawaian PNL.
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- SECTION: TESTIMONI (id="testimoni") -->
    <section id="testimoni" class="py-20 md:py-28 bg-slate-50 border-t border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-bold uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-quote-left text-[11px]"></i> Tanggapan Pengguna
                </div>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Apa Kata Mereka Tentang SIKAT?
                </h2>
                <div class="w-20 h-1 bg-primary-600 mx-auto mt-4 rounded-full"></div>
                <p class="mt-4 text-base sm:text-lg text-slate-600">
                    Pengalaman nyata para koordinator, pimpinan jurusan, dan dosen di Politeknik Negeri Lhokseumawe.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Testimonial 1 -->
                <div class="bg-white rounded-2xl p-7 border border-slate-200/80 shadow-card hover:shadow-elevated transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <!-- Rating Stars -->
                        <div class="flex items-center gap-1 text-amber-400 text-sm mb-4">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="text-slate-700 italic text-sm sm:text-base leading-relaxed mb-6">
                            "Berkat kehadiran sistem SIKAT kami lebih mudah dalam mengelola data kepegawaian dan arsip karena didukung oleh fitur-fitur unggulannya."
                        </p>
                    </div>
                    <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                        <img src="{{ asset('img/fakhruddin.jpeg') }}" alt="Fakhruddin, S.E., M.S.M." class="w-13 h-13 rounded-full object-cover border-2 border-primary-200 shadow-sm" style="width: 52px; height: 52px;" />
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Fakhruddin, S.E., M.S.M.</h4>
                            <p class="text-xs font-medium text-primary-600">Koordinator Bagian Kepegawaian</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="bg-white rounded-2xl p-7 border border-slate-200/80 shadow-card hover:shadow-elevated transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <!-- Rating Stars -->
                        <div class="flex items-center gap-1 text-amber-400 text-sm mb-4">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="text-slate-700 italic text-sm sm:text-base leading-relaxed mb-6">
                            "Dengan sistem SIKAT kami sangat terbantu dalam mengelola data Dosen dan Tendik agar tetap up to date dan valid."
                        </p>
                    </div>
                    <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                        <img src="{{ asset('img/arhami.jpg') }}" alt="Muhammad Arhami, S.Si., M.Kom." class="w-13 h-13 rounded-full object-cover border-2 border-primary-200 shadow-sm" style="width: 52px; height: 52px;" />
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Muhammad Arhami, S.Si., M.Kom.</h4>
                            <p class="text-xs font-medium text-primary-600">Kajur Teknologi Informasi & Komputer</p>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="bg-white rounded-2xl p-7 border border-slate-200/80 shadow-card hover:shadow-elevated transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <!-- Rating Stars -->
                        <div class="flex items-center gap-1 text-amber-400 text-sm mb-4">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="text-slate-700 italic text-sm sm:text-base leading-relaxed mb-6">
                            "Sistem SIKAT mudah digunakan oleh semua usia karena memiliki user interface yang informatif."
                        </p>
                    </div>
                    <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                        <img src="{{ asset('img/davi.jpeg') }}" alt="Muhammad Davi, S.Kom., M.Cs." class="w-13 h-13 rounded-full object-cover border-2 border-primary-200 shadow-sm" style="width: 52px; height: 52px;" />
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Muhammad Davi, S.Kom., M.Cs.</h4>
                            <p class="text-xs font-medium text-primary-600">Dosen Jurusan TIK</p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- SECTION: KONTAK & BANTUAN (id="kontak") -->
    <section id="kontak" class="py-20 md:py-28 gradient-brand text-white relative overflow-hidden">
        <!-- Background Lighting Glow -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-sky-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-sky-300 text-xs font-bold uppercase tracking-wider mb-6">
                <i class="fa-solid fa-headset"></i> Layanan Bantuan & Dukungan
            </div>

            <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight text-white mb-6">
                Ada yang tidak Anda pahami tentang SIKAT?
            </h2>

            <p class="text-slate-300 text-base sm:text-lg max-w-2xl mx-auto mb-10 leading-relaxed">
                Tim Kepegawaian Politeknik Negeri Lhokseumawe siap membantu Anda menyelesaikan kendala akun, pengajuan berkas, maupun pertanyaan seputar sistem.
            </p>

            <!-- WhatsApp Direct Help Button with required test selectors -->
            <div class="inline-block">
                <a href="https://wa.me/6285329583423?text=[SIKAT] Nama%20saya%20...%20Pegawai%20di%20Jurusan/Unit%20...%20ingin%20bertanya%20" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="inline-flex items-center gap-3.5 px-8 py-4 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-base sm:text-lg rounded-2xl shadow-xl shadow-emerald-950/30 hover:shadow-emerald-900/50 hover:no-underline transition-all duration-200 transform hover:-translate-y-1">
                    <i class="fa-brands fa-whatsapp text-2xl"></i>
                    <span>Hubungi Kami via WhatsApp &mdash; Klik Disini!</span>
                </a>
            </div>

            <!-- Contact Information Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-16 text-left">
                <div class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-sky-400 mb-3 text-lg">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <h4 class="text-sm font-bold text-white mb-1">Lokasi Kampus</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Jl. Banda Aceh - Medan Km. 280, Buketrata, Lhokseumawe, Aceh 24301
                    </p>
                </div>

                <div class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-sky-400 mb-3 text-lg">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <h4 class="text-sm font-bold text-white mb-1">Email Resmi</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        kepegawaian@pnl.ac.id<br>sikat@pnl.ac.id
                    </p>
                </div>

                <div class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-sky-400 mb-3 text-lg">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <h4 class="text-sm font-bold text-white mb-1">Jam Pelayanan</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Senin - Jumat: 08:00 - 16:30 WIB<br>(Portal Online 24 Jam)
                    </p>
                </div>
            </div>

        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-slate-900 text-slate-400 border-t border-slate-800 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-10 border-b border-slate-800">
                
                <!-- Col 1: Brand -->
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('img/logo-pnl.png') }}" class="h-10 w-auto" alt="Logo PNL" />
                        <div>
                            <div class="text-lg font-bold text-white">SIKAT PNL</div>
                            <div class="text-xs text-slate-400">Politeknik Negeri Lhokseumawe</div>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                        Sistem Informasi Kepegawaian Terintegrasi untuk efisiensi, akurasi, dan transparansi administrasi sumber daya manusia di Politeknik Negeri Lhokseumawe.
                    </p>
                </div>

                <!-- Col 2: Navigasi Cepat -->
                <div>
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Navigasi</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('landing') }}" class="hover:text-white transition-colors">Beranda</a></li>
                        <li><a href="#sikat" class="hover:text-white transition-colors">Tentang SIKAT</a></li>
                        <li><a href="#layanan" class="hover:text-white transition-colors">Layanan Unggulan</a></li>
                        <li><a href="#statistik" class="hover:text-white transition-colors">Statistik Pegawai</a></li>
                        <li><a href="#faq" class="hover:text-white transition-colors">FAQ</a></li>
                        <li><a href="#testimoni" class="hover:text-white transition-colors">Testimoni</a></li>
                    </ul>
                </div>

                <!-- Col 3: Akses Pegawai -->
                <div>
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Akses Sistem</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Login Akun Pegawai</a></li>
                        @if (Route::has('register'))
                            <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Pendaftaran Akun Baru</a></li>
                        @endif
                        <li><a href="https://pnl.ac.id" target="_blank" rel="noopener noreferrer" class="hover:text-white transition-colors">Website Resmi PNL</a></li>
                    </ul>
                </div>

            </div>

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>&copy; {{ date('Y') }} SIKAT &mdash; Politeknik Negeri Lhokseumawe. Seluruh hak cipta dilindungi.</p>
                <div class="flex items-center gap-4">
                    <span>Versi Modern 2.0</span>
                    <span>&bull;</span>
                    <a href="#header" class="hover:text-slate-300 transition-colors">Kembali ke Atas <i class="fa-solid fa-arrow-up ml-1 text-[10px]"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- INTERSECTION OBSERVER, ACTIVE NAV LINK & CHART.JS SCRIPTS -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const header = document.getElementById("header");
            const sectionNavLinks = document.querySelectorAll("[data-section-link]");
            const observedSections = [];

            function updateSectionNavState(hash) {
                const activeHash = hash && hash.charAt(0) === '#' ? hash : '';

                sectionNavLinks.forEach(function (link) {
                    const isActive = activeHash !== '' && link.getAttribute('href') === activeHash;
                    link.classList.toggle('nav-section-link-active', isActive);

                    if (isActive) {
                        link.setAttribute('aria-current', 'location');
                    } else {
                        link.removeAttribute('aria-current');
                    }
                });
            }

            sectionNavLinks.forEach(function (link) {
                const targetHash = link.getAttribute('href');
                const targetSection = targetHash ? document.querySelector(targetHash) : null;

                if (targetSection) {
                    observedSections.push(targetSection);
                }

                link.addEventListener('click', function () {
                    updateSectionNavState(link.getAttribute('href'));
                });
            });

            updateSectionNavState(window.location.hash);
            window.addEventListener('hashchange', function () {
                updateSectionNavState(window.location.hash);
            });

            if ('IntersectionObserver' in window && observedSections.length > 0) {
                const visibleSections = new Map();

                const sectionObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            visibleSections.set(entry.target.id, {
                                ratio: entry.intersectionRatio,
                                top: entry.boundingClientRect.top
                            });
                        } else {
                            visibleSections.delete(entry.target.id);
                        }
                    });

                    if (visibleSections.size === 0) {
                        return;
                    }

                    let closestPassedSection = null;
                    let closestUpcomingSection = null;

                    visibleSections.forEach(function (state, sectionId) {
                        if (state.top <= 140) {
                            if (closestPassedSection === null
                                || state.top > closestPassedSection.top
                                || (state.top === closestPassedSection.top && state.ratio > closestPassedSection.ratio)) {
                                closestPassedSection = {
                                    id: sectionId,
                                    top: state.top,
                                    ratio: state.ratio
                                };
                            }
                            return;
                        }

                        if (closestUpcomingSection === null
                            || state.top < closestUpcomingSection.top
                            || (state.top === closestUpcomingSection.top && state.ratio > closestUpcomingSection.ratio)) {
                            closestUpcomingSection = {
                                id: sectionId,
                                top: state.top,
                                ratio: state.ratio
                            };
                        }
                    });

                    const activeSectionId = (closestPassedSection || closestUpcomingSection || {}).id || '';
                    if (activeSectionId !== '') {
                        updateSectionNavState('#' + activeSectionId);
                    }
                }, {
                    root: null,
                    rootMargin: '-100px 0px -40% 0px',
                    threshold: [0, 0.15, 0.3, 0.45, 0.6]
                });

                observedSections.forEach(function (section) {
                    sectionObserver.observe(section);
                });
            }

            // Scroll Header Elevation
            window.addEventListener("scroll", function () {
                if (window.scrollY > 20) {
                    header.classList.add("shadow-md", "bg-white/95");
                    header.classList.remove("bg-white/80", "shadow-sm");
                } else {
                    header.classList.remove("shadow-md", "bg-white/95");
                    header.classList.add("bg-white/80", "shadow-sm");
                }
            });

            // CHART.JS INITIALIZATION
            const chartFont = {
                family: "'Plus Jakarta Sans', sans-serif",
                size: 11,
                weight: '500'
            };

            // 1. Chart Jenis Kelamin
            const ctxGender = document.getElementById('chartGender');
            if (ctxGender) {
                const genderLabels = @json($genderChart['labels'] ?? []);
                const genderData = @json($genderChart['data'] ?? []);
                const hasGenderData = genderData.some(val => val > 0);

                new Chart(ctxGender, {
                    type: 'doughnut',
                    data: {
                        labels: genderLabels,
                        datasets: [{
                            data: hasGenderData ? genderData : [1, 1],
                            backgroundColor: hasGenderData ? ['#2563eb', '#ec4899'] : ['#e2e8f0', '#cbd5e1'],
                            hoverBackgroundColor: hasGenderData ? ['#1d4ed8', '#db2777'] : ['#cbd5e1', '#94a3b8'],
                            borderWidth: 3,
                            borderColor: '#ffffff',
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: chartFont,
                                    usePointStyle: true,
                                    padding: 16
                                }
                            },
                            tooltip: {
                                enabled: hasGenderData,
                                callbacks: {
                                    label: function (context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const value = context.raw || 0;
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return ` ${context.label}: ${value} Pegawai (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 2. Chart Golongan
            const ctxGolongan = document.getElementById('chartGolongan');
            if (ctxGolongan) {
                const golonganLabels = @json($golonganChart['labels'] ?? []);
                const golonganData = @json($golonganChart['data'] ?? []);
                const hasGolonganData = golonganData.some(val => val > 0);

                new Chart(ctxGolongan, {
                    type: 'bar',
                    data: {
                        labels: hasGolonganData ? golonganLabels : ['Belum Ada Data'],
                        datasets: [{
                            label: 'Jumlah Pegawai',
                            data: hasGolonganData ? golonganData : [0],
                            backgroundColor: '#6366f1',
                            hoverBackgroundColor: '#4f46e5',
                            borderRadius: 6,
                            barThickness: hasGolonganData && golonganLabels.length > 8 ? 16 : 24,
                            maxBarThickness: 32
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return ` ${context.raw} Pegawai`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: chartFont, color: '#64748b' }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    precision: 0,
                                    font: chartFont,
                                    color: '#64748b'
                                }
                            }
                        }
                    }
                });
            }

            // 3. Chart Pendidikan
            const ctxPendidikan = document.getElementById('chartPendidikan');
            if (ctxPendidikan) {
                const pendidikanLabels = @json($pendidikanChart['labels'] ?? []);
                const pendidikanData = @json($pendidikanChart['data'] ?? []);
                const hasPendidikanData = pendidikanData.some(val => val > 0);

                const pendidikanColors = [
                    '#0ea5e9', '#06b6d4', '#10b981', '#14b8a6', '#3b82f6', '#8b5cf6', '#a855f7'
                ];

                new Chart(ctxPendidikan, {
                    type: 'bar',
                    data: {
                        labels: hasPendidikanData ? pendidikanLabels : ['Belum Ada Data'],
                        datasets: [{
                            label: 'Jumlah Pegawai',
                            data: hasPendidikanData ? pendidikanData : [0],
                            backgroundColor: hasPendidikanData ? pendidikanColors.slice(0, pendidikanLabels.length) : '#e2e8f0',
                            borderRadius: 6,
                            barThickness: hasPendidikanData && pendidikanLabels.length > 6 ? 18 : 28,
                            maxBarThickness: 36
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return ` ${context.raw} Pegawai`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                ticks: { precision: 0, font: chartFont, color: '#64748b' }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { font: chartFont, color: '#64748b' }
                            }
                        }
                    }
                });
            }

            // 4. Chart Eselon
            const ctxEselon = document.getElementById('chartEselon');
            if (ctxEselon) {
                const eselonLabels = @json($eselonChart['labels'] ?? []);
                const eselonData = @json($eselonChart['data'] ?? []);
                const hasEselonData = eselonData.some(val => val > 0);

                const eselonColors = [
                    '#f59e0b', '#f97316', '#ef4444', '#ec4899', '#8b5cf6', '#64748b', '#0284c7'
                ];

                new Chart(ctxEselon, {
                    type: 'bar',
                    data: {
                        labels: hasEselonData ? eselonLabels : ['Belum Ada Data'],
                        datasets: [{
                            label: 'Jumlah Pegawai',
                            data: hasEselonData ? eselonData : [0],
                            backgroundColor: hasEselonData ? eselonColors.slice(0, eselonLabels.length) : '#e2e8f0',
                            borderRadius: 6,
                            barThickness: 24,
                            maxBarThickness: 34
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        return ` ${context.raw} Pegawai`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: chartFont, color: '#64748b' }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    precision: 0,
                                    font: chartFont,
                                    color: '#64748b'
                                }
                            }
                        }
                    }
                });
            }

        });
    </script>
</body>
</html>
