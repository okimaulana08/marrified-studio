<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Marrified Studio</title>
    @vite(['resources/css/app.css'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600,700|petit-formal-script:400|space-grotesk:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #e83e8c;
            --brand-dark: #c82c75;
            --brand-soft: #ff6b9d;
            --coral: #ff7a85;
            --bg-tint: #fff5f7;
            --ink: #2b1320;
            --ink-soft: #6b4858;
            --ink-mute: #9b7888;
            --line: rgba(232,62,140,0.18);
        }
        [x-cloak] { display: none !important; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; overflow-x: hidden; }
        body {
            font-family: 'Space Grotesk', system-ui, sans-serif;
            background: #fff;
            color: var(--ink);
            min-height: 100vh;
            position: relative;
        }

        /* ────────────────  LAYOUT GRID ──────────────── */
        .auth-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr;
            position: relative;
        }
        @media (min-width: 1024px) {
            .auth-shell { grid-template-columns: 1.15fr 1fr; }
        }

        /* ────────────────  LEFT: BRAND PANEL  (desktop only) ──────────────── */
        .brand-panel {
            display: none;
        }
        @media (min-width: 1024px) {
            .brand-panel {
                position: relative;
                padding: 4rem 5rem;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                justify-content: center;
                background:
                    radial-gradient(ellipse 80% 60% at 20% 0%, rgba(180,40,95,0.45), transparent 60%),
                    radial-gradient(ellipse 70% 60% at 90% 100%, rgba(200,70,90,0.40), transparent 60%),
                    linear-gradient(135deg, #b8326b 0%, #c45567 55%, #d98a93 100%);
            }
        }
        .brand-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(rgba(255,255,255,0.35) 1px, transparent 1px);
            background-size: 18px 18px;
            mask-image: radial-gradient(ellipse at center, black 30%, transparent 80%);
            pointer-events: none;
            z-index: 0;
        }

        .brand-eyebrow {
            position: relative; z-index: 5;
            font-size: 0.7rem;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.95);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1.5rem;
        }
        .brand-eyebrow::before {
            content: '';
            display: inline-block;
            width: 24px; height: 1px;
            background: rgba(255,255,255,0.7);
        }

        .brand-illustration {
            position: relative; z-index: 5;
            width: clamp(160px, 22vw, 240px);
            height: clamp(160px, 22vw, 240px);
            margin: 0 0 2rem;
            animation: float-slow 6s ease-in-out infinite;
        }
        @keyframes float-slow {
            0%,100% { transform: translateY(0); }
            50%     { transform: translateY(-12px); }
        }
        .brand-illustration .ring-a, .brand-illustration .ring-b {
            transform-origin: center;
        }
        .brand-illustration .ring-a { animation: ring-spin-a 14s linear infinite; }
        .brand-illustration .ring-b { animation: ring-spin-b 18s linear infinite reverse; }
        @keyframes ring-spin-a { to { transform: rotate(360deg); } }
        @keyframes ring-spin-b { to { transform: rotate(-360deg); } }

        .brand-title {
            position: relative; z-index: 5;
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(1.75rem, 4vw, 2.6rem);
            font-weight: 700;
            line-height: 1.15;
            letter-spacing: -0.02em;
            color: white;
            margin-bottom: 0.4rem;
            max-width: 540px;
            text-shadow: 0 2px 12px rgba(200,40,100,0.25);
        }
        .brand-title .accent {
            background: linear-gradient(135deg, #fff 0%, #ffe6ee 60%, #fff 120%);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: gradient-shift 6s ease-in-out infinite;
        }
        @keyframes gradient-shift {
            0%,100% { background-position: 0% 50%; }
            50%     { background-position: 100% 50%; }
        }
        .brand-tagline {
            position: relative; z-index: 5;
            color: rgba(255,255,255,0.85);
            font-size: 0.95rem;
            line-height: 1.7;
            max-width: 480px;
            margin-bottom: 2rem;
        }
        .brand-pills {
            position: relative; z-index: 5;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }
        .pill {
            padding: 0.4rem 0.85rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            border: 1px solid rgba(255,255,255,0.5);
            background: rgba(255,255,255,0.18);
            color: #fff;
            backdrop-filter: blur(6px);
        }
        .pill--emerald { background: rgba(255,255,255,0.22); }
        .pill--pink    { background: rgba(255,255,255,0.18); }
        .pill--amber   { background: rgba(255,255,255,0.14); }

        .brand-foot {
            position: absolute;
            bottom: 1.5rem; left: 2rem; right: 2rem;
            z-index: 5;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.7);
        }
        @media (min-width: 1024px) {
            .brand-foot { left: 5rem; right: 5rem; }
        }

        /* ────────────────  FLOATING FLOWERS  ──────────────── */
        .flower {
            position: absolute;
            pointer-events: none;
            opacity: 0.55;
            z-index: 1;
            animation: flower-drift 24s ease-in-out infinite;
            filter: drop-shadow(0 2px 6px rgba(255,255,255,0.3));
        }
        .flower--1 { top: 8%;  left: 12%; width: 56px; animation-delay: 0s; }
        .flower--2 { top: 20%; right: 18%; width: 36px; animation-delay: -6s; }
        .flower--3 { bottom: 30%; left: 8%; width: 44px; animation-delay: -12s; }
        .flower--4 { top: 55%; left: 45%; width: 28px; animation-delay: -3s; opacity: 0.5; }
        .flower--5 { bottom: 12%; right: 12%; width: 50px; animation-delay: -18s; }
        .flower--6 { top: 38%; right: 6%; width: 32px; animation-delay: -9s; opacity: 0.6; }
        @keyframes flower-drift {
            0%   { transform: translate(0,0) rotate(0deg); }
            25%  { transform: translate(20px,-25px) rotate(60deg); }
            50%  { transform: translate(-15px,-50px) rotate(180deg); }
            75%  { transform: translate(-25px,-20px) rotate(280deg); }
            100% { transform: translate(0,0) rotate(360deg); }
        }

        /* ────────────────  RIGHT: FORM PANEL ──────────────── */
        .form-panel {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            background:
                radial-gradient(ellipse 90% 70% at 50% 0%, rgba(232,62,140,0.10), transparent 70%),
                radial-gradient(ellipse 60% 50% at 100% 100%, rgba(255,122,133,0.10), transparent 65%),
                linear-gradient(180deg, #fff5f7 0%, #ffffff 60%);
        }
        @media (min-width: 1024px) {
            .form-panel { padding: 4rem 5rem; }
        }

        .form-card {
            width: 100%;
            max-width: 420px;
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(24px) saturate(160%);
            -webkit-backdrop-filter: blur(24px) saturate(160%);
            border: 1px solid rgba(232,62,140,0.15);
            border-radius: 1.5rem;
            padding: 2.25rem 2rem;
            box-shadow:
                0 24px 60px -12px rgba(232,62,140,0.18),
                0 4px 16px -4px rgba(232,62,140,0.08),
                inset 0 1px 0 rgba(255,255,255,0.9);
            animation: card-rise 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) backwards;
        }
        @keyframes card-rise {
            from { opacity: 0; transform: translateY(20px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .form-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .form-mark {
            width: 44px; height: 44px;
            border-radius: 0.75rem;
            background: linear-gradient(135deg, #e83e8c 0%, #ff7a85 100%);
            box-shadow:
                0 8px 20px -4px rgba(232,62,140,0.45),
                inset 0 1px 0 rgba(255,255,255,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .form-brand {
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: -0.01em;
            color: var(--ink);
        }
        .form-brand small {
            display: block;
            font-weight: 500;
            font-size: 0.7rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--brand);
            margin-top: 0.1rem;
        }

        .form-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 0.35rem;
            letter-spacing: -0.01em;
        }
        .form-subtitle {
            color: var(--ink-soft);
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
        }

        /* Input field with icon prefix */
        .auth-field { margin-bottom: 1rem; }
        .auth-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--ink-soft);
            margin-bottom: 0.5rem;
        }
        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 0.75rem;
            box-shadow: 0 1px 2px rgba(232,62,140,0.04);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .input-wrap:focus-within {
            background: #fff;
            border-color: var(--brand);
            box-shadow:
                0 1px 2px rgba(232,62,140,0.08),
                0 0 0 4px rgba(232,62,140,0.12);
        }
        .input-wrap.error { border-color: rgba(239,68,68,0.55); }
        .input-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            color: var(--ink-mute);
            flex-shrink: 0;
        }
        .input-wrap:focus-within .input-icon { color: var(--brand); }
        .auth-input {
            flex: 1;
            background: transparent;
            border: 0;
            outline: none;
            color: var(--ink);
            font-size: 0.92rem;
            padding: 0.78rem 0.6rem 0.78rem 0;
            min-width: 0;
        }
        .auth-input::placeholder { color: rgba(155,120,136,0.55); }

        /* Override Chrome/Safari autofill yellow + white-bg styling so it
         * stays consistent with the dark glass form. The 9999s transition
         * is a hack to keep the fake bg from animating back to default. */
        /* Hide native password reveal/clear glyphs (Edge ms-reveal, Edge ms-clear,
         * and Chrome/Safari built-in caps-lock warning). Our custom eye toggle
         * is the only reveal control. */
        .auth-input::-ms-reveal,
        .auth-input::-ms-clear { display: none; }
        .auth-input::-webkit-credentials-auto-fill-button,
        .auth-input::-webkit-strong-password-auto-fill-button {
            visibility: hidden !important;
            display: none !important;
            pointer-events: none;
            position: absolute;
            right: 0;
        }
        .auth-input::-webkit-caps-lock-indicator { display: none; }

        .auth-input:-webkit-autofill,
        .auth-input:-webkit-autofill:hover,
        .auth-input:-webkit-autofill:focus,
        .auth-input:-webkit-autofill:active {
            -webkit-text-fill-color: #2b1320 !important;
            -webkit-box-shadow: 0 0 0 1000px #fff inset !important;
            box-shadow: 0 0 0 1000px #fff inset !important;
            caret-color: #2b1320;
            transition: background-color 9999s ease-in-out 0s;
            border-radius: 0;
        }

        .pw-toggle {
            background: transparent;
            border: 0;
            color: var(--ink-mute);
            cursor: pointer;
            padding: 0 0.85rem 0 0.6rem;
            display: flex;
            align-items: center;
            transition: color 0.15s ease;
        }
        .pw-toggle:hover { color: var(--brand); }

        .auth-error {
            color: #dc2626;
            font-size: 0.72rem;
            margin-top: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        .auth-error::before { content: '⚠'; }

        .form-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0.4rem 0 1.4rem;
        }
        .auth-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--ink-soft);
            font-size: 0.82rem;
            cursor: pointer;
            user-select: none;
        }
        .auth-checkbox input { width: 1rem; height: 1rem; accent-color: var(--brand); cursor: pointer; }
        .form-row .help-link {
            font-size: 0.78rem;
            color: var(--brand);
            text-decoration: none;
            font-weight: 600;
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* Submit button */
        .auth-submit {
            position: relative;
            width: 100%;
            padding: 0.92rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #e83e8c 0%, #ff7a85 100%);
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.05em;
            border: 0;
            border-radius: 0.85rem;
            cursor: pointer;
            box-shadow:
                0 8px 24px -4px rgba(232,62,140,0.45),
                inset 0 1px 0 rgba(255,255,255,0.3);
            overflow: hidden;
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .auth-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,0.4) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.7s ease;
        }
        .auth-submit:hover {
            transform: translateY(-1.5px);
            box-shadow:
                0 12px 32px -4px rgba(232,62,140,0.55),
                inset 0 1px 0 rgba(255,255,255,0.4);
        }
        .auth-submit:hover::before { transform: translateX(100%); }
        .auth-submit:active { transform: translateY(0); }

        .form-foot {
            text-align: center;
            color: var(--ink-mute);
            font-size: 0.72rem;
            margin-top: 1.4rem;
        }
        .form-foot strong { color: var(--brand); font-weight: 600; }

        @media (prefers-reduced-motion: reduce) {
            .flower, .brand-illustration, .ring-a, .ring-b, .form-card, .accent { animation: none !important; }
        }
    </style>
</head>
<body>
    {{-- Reusable flower SVG (5-petal sakura). Pakai <symbol> + <use> supaya
         tag <use> tinggal tunjuk dengan id, hemat markup. --}}
    <svg width="0" height="0" style="position: absolute;" aria-hidden="true">
        <defs>
            <radialGradient id="flower-pink" cx="50%" cy="100%" r="80%">
                <stop offset="0%" stop-color="#fbcfe8" stop-opacity="1"/>
                <stop offset="100%" stop-color="#f472b6" stop-opacity="0.7"/>
            </radialGradient>
            <radialGradient id="flower-cream" cx="50%" cy="100%" r="80%">
                <stop offset="0%" stop-color="#fef9c3" stop-opacity="1"/>
                <stop offset="100%" stop-color="#fde68a" stop-opacity="0.7"/>
            </radialGradient>
            <radialGradient id="flower-emerald" cx="50%" cy="100%" r="80%">
                <stop offset="0%" stop-color="#a7f3d0" stop-opacity="1"/>
                <stop offset="100%" stop-color="#34d399" stop-opacity="0.6"/>
            </radialGradient>
            <symbol id="sakura" viewBox="0 0 60 60">
                <g transform="translate(30 30)">
                    {{-- 5 petals at 72° intervals --}}
                    <ellipse cx="0" cy="-12" rx="6.5" ry="11" fill="var(--petal-fill, url(#flower-pink))" opacity="0.9"/>
                    <ellipse cx="0" cy="-12" rx="6.5" ry="11" fill="var(--petal-fill, url(#flower-pink))" opacity="0.9" transform="rotate(72)"/>
                    <ellipse cx="0" cy="-12" rx="6.5" ry="11" fill="var(--petal-fill, url(#flower-pink))" opacity="0.9" transform="rotate(144)"/>
                    <ellipse cx="0" cy="-12" rx="6.5" ry="11" fill="var(--petal-fill, url(#flower-pink))" opacity="0.9" transform="rotate(216)"/>
                    <ellipse cx="0" cy="-12" rx="6.5" ry="11" fill="var(--petal-fill, url(#flower-pink))" opacity="0.9" transform="rotate(288)"/>
                    {{-- Stamen --}}
                    <circle r="3.5" fill="#fef3c7"/>
                    <circle r="1.8" fill="#fcd34d"/>
                </g>
            </symbol>
        </defs>
    </svg>

    <div class="auth-shell">
        {{-- ────────────  LEFT: BRAND PANEL  ──────────── --}}
        <section class="brand-panel">
            {{-- Floating sakura flowers --}}
            <svg class="flower flower--1" style="--petal-fill: url(#flower-pink);" viewBox="0 0 60 60" aria-hidden="true"><use href="#sakura"/></svg>
            <svg class="flower flower--2" style="--petal-fill: url(#flower-cream);" viewBox="0 0 60 60" aria-hidden="true"><use href="#sakura"/></svg>
            <svg class="flower flower--3" style="--petal-fill: url(#flower-emerald);" viewBox="0 0 60 60" aria-hidden="true"><use href="#sakura"/></svg>
            <svg class="flower flower--4" style="--petal-fill: url(#flower-pink);" viewBox="0 0 60 60" aria-hidden="true"><use href="#sakura"/></svg>
            <svg class="flower flower--5" style="--petal-fill: url(#flower-cream);" viewBox="0 0 60 60" aria-hidden="true"><use href="#sakura"/></svg>
            <svg class="flower flower--6" style="--petal-fill: url(#flower-emerald);" viewBox="0 0 60 60" aria-hidden="true"><use href="#sakura"/></svg>

            <p class="brand-eyebrow">Wedding Invitation Studio</p>

            {{-- Two interlocking rings illustration --}}
            <svg class="brand-illustration" viewBox="0 0 240 240" fill="none" aria-hidden="true">
                <defs>
                    <linearGradient id="ring-grad-a" x1="0" y1="0" x2="240" y2="240">
                        <stop offset="0%" stop-color="#ffffff"/>
                        <stop offset="100%" stop-color="#ffe6ee"/>
                    </linearGradient>
                    <linearGradient id="ring-grad-b" x1="0" y1="0" x2="240" y2="240">
                        <stop offset="0%" stop-color="#ffffff"/>
                        <stop offset="100%" stop-color="#fff0f3"/>
                    </linearGradient>
                </defs>
                {{-- Outer dashed ornament ring --}}
                <circle class="ring-a" cx="120" cy="120" r="108" stroke="url(#ring-grad-a)" stroke-width="1.5"
                        stroke-dasharray="3 8" fill="none" opacity="0.5"/>

                {{-- Two interlocking solid rings --}}
                <g class="ring-b">
                    <circle cx="100" cy="120" r="56" stroke="url(#ring-grad-a)" stroke-width="6" fill="none"/>
                    <circle cx="140" cy="120" r="56" stroke="url(#ring-grad-b)" stroke-width="6" fill="none" opacity="0.85"/>
                </g>

                {{-- Heart on top --}}
                <path d="M120 80 C115 70, 102 70, 102 84 C102 95, 120 105, 120 105 C120 105, 138 95, 138 84 C138 70, 125 70, 120 80Z"
                      fill="url(#ring-grad-b)" opacity="0.9"/>

                {{-- Sparkles --}}
                <circle cx="36" cy="60" r="3" fill="#fcd34d"/>
                <circle cx="200" cy="180" r="3" fill="#fcd34d"/>
                <circle cx="210" cy="50" r="2" fill="#a7f3d0"/>
                <circle cx="40" cy="200" r="2" fill="#fbcfe8"/>
            </svg>

            <h1 class="brand-title">
                Selamat Datang di<br>
                <span class="accent">Marrified Studio</span>
            </h1>
            <p class="brand-tagline">
                Platform manajemen undangan pernikahan elegan — pilih tema watercolor,
                kelola couple info, daftar tamu, dan share link bertoken ke setiap undangan.
            </p>

            <div class="brand-pills">
                <span class="pill pill--emerald">Tema Watercolor</span>
                <span class="pill pill--pink">Multi-Variant</span>
                <span class="pill pill--amber">Live Preview</span>
            </div>

            <footer class="brand-foot">
                <span>© {{ date('Y') }} Marrified Studio</span>
                <span style="opacity:0.6">v1.0 PoC</span>
            </footer>
        </section>

        {{-- ────────────  RIGHT: FORM PANEL  ──────────── --}}
        <section class="form-panel">
            <div class="form-card">
                <div class="form-header">
                    <div class="form-mark" aria-hidden="true">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 18 V6 L12 13 L19 6 V18"/>
                        </svg>
                    </div>
                    <div class="form-brand">
                        Marrified
                        <small>Studio</small>
                    </div>
                </div>

                <h2 class="form-title">Masuk ke akun Anda</h2>
                <p class="form-subtitle">Silakan masukkan kredensial untuk melanjutkan.</p>

                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="auth-field">
                        <label class="auth-label" for="email">Email</label>
                        <div class="input-wrap @error('email') error @enderror">
                            <span class="input-icon" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="5" width="18" height="14" rx="2"/>
                                    <path d="M3 7l9 6 9-6"/>
                                </svg>
                            </span>
                            <input id="email" name="email" type="email" value="{{ old('email') }}"
                                   class="auth-input"
                                   autocomplete="email" required autofocus
                                   placeholder="couple@example.test">
                        </div>
                        @error('email') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="auth-field">
                        <label class="auth-label" for="password">Password</label>
                        <div class="input-wrap @error('password') error @enderror">
                            <span class="input-icon" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="4" y="11" width="16" height="10" rx="2"/>
                                    <path d="M8 11V7a4 4 0 018 0v4"/>
                                </svg>
                            </span>
                            <input id="password" name="password" type="password"
                                   class="auth-input"
                                   autocomplete="current-password" required
                                   placeholder="••••••••••••">
                            <button type="button" class="pw-toggle" data-pw-toggle
                                    aria-label="Tampilkan password">
                                <svg data-eye width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <svg data-eye-off hidden width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                                    <line x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        @error('password') <p class="auth-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-row">
                        <label class="auth-checkbox">
                            <input type="checkbox" name="remember" value="1">
                            Ingat saya
                        </label>
                        <span class="help-link" title="Hubungi admin untuk reset password">Lupa password?</span>
                    </div>

                    <button type="submit" class="auth-submit">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/>
                        </svg>
                        Masuk
                    </button>
                </form>

                <p class="form-foot">
                    Akun couple di-generate oleh <strong>tim studio</strong>.
                </p>
            </div>
        </section>
    </div>

    @vite(['resources/js/app.js'])
    <script>
        document.querySelectorAll('[data-pw-toggle]').forEach(btn => {
            btn.addEventListener('click', () => {
                const wrap = btn.closest('.input-wrap');
                const input = wrap.querySelector('input');
                const eye = btn.querySelector('[data-eye]');
                const eyeOff = btn.querySelector('[data-eye-off]');
                const showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                eye.hidden = !showing;
                eyeOff.hidden = showing;
                btn.setAttribute('aria-label', showing ? 'Tampilkan password' : 'Sembunyikan password');
            });
        });
    </script>
</body>
</html>
