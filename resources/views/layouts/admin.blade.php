<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Theme Manager') — Marrified Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    {{-- Preload all curated fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,700|cormorant-garamond:400,500,600|libre-baskerville:400,700|eb-garamond:400,500|crimson-text:400,600|lato:300,400,700|poppins:300,400,500,600|montserrat:400,500,600|raleway:400,500,600|josefin-sans:300,400,600|petit-formal-script:400|dancing-script:400,600|great-vibes:400|parisienne:400|sacramento:400|alex-brush:400|space-grotesk:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --emerald-glow: 232 62 140;
            --teal-glow: 255 122 133;
            --indigo-glow: 99 102 241;
            --pink-glow: 236 72 153;
            --amber-glow: 251 191 36;
        }

        [x-cloak] { display: none !important; }

        /* Tailwind v4 resolves emerald/teal utilities through these CSS vars.
         * Overriding them here flips every emerald-* and teal-* class to pink
         * without touching markup. */
        :root, :host {
            --color-emerald-50:  #fff5f7;
            --color-emerald-100: #ffe6ee;
            --color-emerald-200: #ffd0dd;
            --color-emerald-300: #ffa8c1;
            --color-emerald-400: #ff7a9e;
            --color-emerald-500: #e83e8c;
            --color-emerald-600: #d63384;
            --color-emerald-700: #c82c75;
            --color-emerald-800: #a32463;
            --color-emerald-900: #7a1a4a;
            --color-emerald-950: #4d1130;

            --color-teal-50:  #fff4f5;
            --color-teal-100: #ffe2e5;
            --color-teal-200: #ffc7cc;
            --color-teal-300: #ffa1aa;
            --color-teal-400: #ff7a85;
            --color-teal-500: #ff5d6c;
            --color-teal-600: #e84455;
            --color-teal-700: #c8324a;
            --color-teal-800: #a3263c;
            --color-teal-900: #7a1c2e;
        }

        /* Remap Tailwind emerald + teal utilities to the pink brand palette so
         * existing markup keeps working without per-class edits. */
        [class*="text-emerald-"]   { color: #ff7a85 !important; }
        [class*="text-teal-"]      { color: #ff7a85 !important; }
        [class*="bg-emerald-"]     { background-color: rgba(232,62,140,0.15) !important; }
        [class*="bg-teal-"]        { background-color: rgba(255,122,133,0.15) !important; }
        .bg-emerald-400, .bg-emerald-500, .bg-emerald-600 { background-color: #e83e8c !important; }
        .bg-teal-400,    .bg-teal-500,    .bg-teal-600    { background-color: #ff7a85 !important; }
        [class*="border-emerald-"] { border-color: rgba(232,62,140,0.4) !important; }
        [class*="border-teal-"]    { border-color: rgba(255,122,133,0.4) !important; }
        [class*="ring-emerald-"]   { --tw-ring-color: rgba(232,62,140,0.5) !important; }
        [class*="ring-teal-"]      { --tw-ring-color: rgba(255,122,133,0.5) !important; }
        [class*="from-emerald-"]   { --tw-gradient-from: #e83e8c !important; }
        [class*="to-emerald-"]     { --tw-gradient-to:   #ff7a85 !important; }
        [class*="via-emerald-"]    { --tw-gradient-via:  #e83e8c !important; }
        [class*="from-teal-"]      { --tw-gradient-from: #ff7a85 !important; }
        [class*="to-teal-"]        { --tw-gradient-to:   #ff9aa2 !important; }
        [class*="shadow-emerald-"] { --tw-shadow-color: rgba(232,62,140,0.5) !important; }
        [class*="accent-emerald-"] { accent-color: #e83e8c !important; }
        [class*="fill-emerald-"]   { fill: #e83e8c !important; }
        [class*="stroke-emerald-"] { stroke: #e83e8c !important; }
        [class*="decoration-emerald-"] { text-decoration-color: #e83e8c !important; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Space Grotesk', 'Inter', system-ui, -apple-system, sans-serif;
            font-feature-settings: "ss01", "cv01", "cv11";
        }

        /* ==================== GLASS TIERS ==================== */
        .glass-subtle {
            background: linear-gradient(135deg, rgba(255,255,255,0.03) 0%, rgba(255,255,255,0.01) 100%);
            backdrop-filter: blur(8px) saturate(140%);
            -webkit-backdrop-filter: blur(8px) saturate(140%);
            border: 1px solid rgba(255,255,255,0.06);
        }
        .glass-sm {
            background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 100%);
            backdrop-filter: blur(12px) saturate(150%);
            -webkit-backdrop-filter: blur(12px) saturate(150%);
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.06);
        }
        .glass {
            background:
                linear-gradient(135deg, rgba(255,255,255,0.07) 0%, rgba(255,255,255,0.03) 100%),
                radial-gradient(at top left, rgba(232,62,140,0.04) 0%, transparent 50%);
            backdrop-filter: blur(20px) saturate(160%);
            -webkit-backdrop-filter: blur(20px) saturate(160%);
            border: 1px solid rgba(255,255,255,0.10);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.08),
                0 4px 24px -4px rgba(0,0,0,0.2);
        }
        .glass-strong {
            background:
                linear-gradient(135deg, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0.05) 100%),
                radial-gradient(at top right, rgba(99,102,241,0.06) 0%, transparent 50%);
            backdrop-filter: blur(28px) saturate(180%);
            -webkit-backdrop-filter: blur(28px) saturate(180%);
            border: 1px solid rgba(255,255,255,0.16);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.12),
                0 12px 40px -8px rgba(0,0,0,0.4);
        }

        /* ==================== INPUTS ==================== */
        .admin-input {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.10);
            color: white;
            border-radius: 0.75rem;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
        }
        .admin-input:hover { background: rgba(255,255,255,0.07); border-color: rgba(255,255,255,0.16); }
        .admin-input:focus {
            outline: none;
            background: rgba(255,255,255,0.08);
            border-color: rgb(var(--emerald-glow) / 0.6);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.06),
                0 0 0 4px rgb(var(--emerald-glow) / 0.12),
                0 0 20px -4px rgb(var(--emerald-glow) / 0.3);
        }
        .admin-input::placeholder { color: rgba(255,255,255,0.25); }
        .admin-input:read-only { opacity: 0.5; cursor: not-allowed; }

        .admin-select {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.10);
            color: white;
            border-radius: 0.75rem;
            appearance: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        .admin-select:hover { background: rgba(255,255,255,0.07); border-color: rgba(255,255,255,0.16); }
        .admin-select:focus {
            outline: none;
            border-color: rgb(var(--emerald-glow) / 0.6);
            box-shadow: 0 0 0 4px rgb(var(--emerald-glow) / 0.12);
        }
        .admin-select option, .admin-select optgroup { background: #0f172a; color: white; }
        .admin-select optgroup { color: rgb(var(--emerald-glow)); font-weight: 600; }

        /* ==================== BUTTONS ==================== */
        .btn-primary {
            position: relative;
            background: linear-gradient(135deg, #c82c75 0%, #e83e8c 100%);
            color: white;
            font-weight: 600;
            border-radius: 0.75rem;
            padding: 0.5rem 1.25rem;
            font-size: 0.875rem;
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow:
                0 4px 16px -4px rgba(232,62,140,0.5),
                inset 0 1px 0 rgba(255,255,255,0.2);
            overflow: hidden;
        }
        .btn-primary::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,0.4) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }
        .btn-primary:hover::before { transform: translateX(100%); }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow:
                0 8px 24px -4px rgba(232,62,140,0.6),
                inset 0 1px 0 rgba(255,255,255,0.3);
        }
        .btn-primary:disabled {
            opacity: 0.35;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .btn-primary:disabled::before { display: none; }

        .btn-ghost {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.6);
            font-weight: 500;
            border-radius: 0.75rem;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        .btn-ghost:hover {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.15);
            color: white;
        }

        /* ==================== FILE PICKER BUTTON ====================
         * Generic styled <label> that wraps a hidden <input type="file">.
         * Looks like a button, shows selected filename once chosen. Pair
         * the label with `<input ... class="sr-only">`. Reusable for any
         * single-file picker that wants to look like a real button. */
        .file-pick-btn {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 0.65rem;
            color: rgba(255,255,255,0.85);
            font-weight: 500;
            transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 38px;
        }
        .file-pick-btn:hover {
            background: rgba(232,62,140,0.10);
            border-color: rgba(232,62,140,0.4);
            color: white;
        }
        .file-pick-btn:focus-within {
            outline: none;
            border-color: rgba(232,62,140,0.6);
            box-shadow: 0 0 0 3px rgba(232,62,140,0.15);
        }

        /* ==================== PHOTO DROPZONE ==================== */
        .photo-dropzone {
            position: relative;
            width: 100%;
            aspect-ratio: 1;
            border-radius: 0.85rem;
            overflow: hidden;
            cursor: pointer;
            background: rgba(255,255,255,0.03);
            border: 1.5px dashed rgba(255,255,255,0.18);
            transition: all 0.2s ease;
        }
        .photo-dropzone:hover {
            background: rgba(232,62,140,0.06);
            border-color: rgba(232,62,140,0.45);
        }
        .photo-dropzone--has-image {
            border-style: solid;
            border-color: rgba(255,255,255,0.12);
            background: transparent;
        }
        .photo-dropzone-label {
            display: flex;
            width: 100%;
            height: 100%;
            cursor: pointer;
            position: relative;
        }
        .photo-dropzone-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            text-align: center;
            padding: 1rem;
        }
        .photo-dropzone-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .photo-dropzone-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            color: white;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: linear-gradient(135deg, rgba(232,62,140,0.55) 0%, rgba(0,0,0,0.5) 100%);
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .photo-dropzone--has-image:hover .photo-dropzone-overlay { opacity: 1; }

        /* ==================== TYPOGRAPHY ==================== */
        .text-gradient {
            background: linear-gradient(135deg, #ffffff 0%, #d1fae5 50%, #a7f3d0 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            color: transparent;
        }
        .text-gradient-emerald {
            background: linear-gradient(135deg, #e83e8c 0%, #ff7a85 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .text-gradient-amber {
            background: linear-gradient(135deg, #fcd34d 0%, #fb923c 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .font-display { font-family: 'Playfair Display', serif; }
        .tracking-display { letter-spacing: -0.025em; }

        /* ==================== EFFECTS ==================== */
        .card-lift {
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1),
                        box-shadow 0.3s ease,
                        border-color 0.2s ease;
        }
        .card-lift:hover {
            transform: translateY(-4px);
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,0.1),
                0 16px 40px -8px rgba(0,0,0,0.5),
                0 0 32px -8px rgba(232,62,140,0.2);
            border-color: rgba(255,255,255,0.18);
        }

        .glow-emerald {
            box-shadow: 0 0 24px -4px rgba(232,62,140,0.4);
        }
        .glow-emerald-strong {
            box-shadow:
                0 0 32px -4px rgba(232,62,140,0.5),
                0 0 0 1px rgba(232,62,140,0.3);
        }

        /* Animated dirty pulse */
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(251,191,36,0.6); }
            50% { box-shadow: 0 0 0 6px rgba(251,191,36,0); }
        }
        .pulse-amber {
            animation: pulse-glow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Gradient border */
        .border-gradient {
            position: relative;
            background-clip: padding-box;
            border: 1px solid transparent;
        }
        .border-gradient::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: -1;
            margin: -1px;
            border-radius: inherit;
            background: linear-gradient(135deg, rgba(232,62,140,0.4), rgba(99,102,241,0.4));
        }

        /* ==================== AURORA BACKGROUND ==================== */
        @keyframes aurora-1 {
            0%, 100% { transform: translate3d(-15%, -10%, 0) rotate(0deg) scale(1); }
            33% { transform: translate3d(20%, 15%, 0) rotate(120deg) scale(1.15); }
            66% { transform: translate3d(-5%, 25%, 0) rotate(240deg) scale(0.95); }
        }
        @keyframes aurora-2 {
            0%, 100% { transform: translate3d(10%, 5%, 0) rotate(0deg) scale(1.05); }
            50% { transform: translate3d(-25%, -15%, 0) rotate(180deg) scale(1.2); }
        }
        @keyframes aurora-3 {
            0%, 100% { transform: translate3d(0, 0, 0) scale(1); opacity: 0.18; }
            50% { transform: translate3d(15%, -10%, 0) scale(1.1); opacity: 0.28; }
        }

        .aurora-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            will-change: transform;
        }
        .aurora-1 {
            top: -10%; left: -10%;
            width: 50vw; height: 50vw;
            background: radial-gradient(circle, rgba(232,62,140,0.35) 0%, transparent 70%);
            animation: aurora-1 32s ease-in-out infinite;
        }
        .aurora-2 {
            top: 30%; right: -15%;
            width: 45vw; height: 45vw;
            background: radial-gradient(circle, rgba(99,102,241,0.22) 0%, transparent 70%);
            animation: aurora-2 40s ease-in-out infinite;
        }
        .aurora-3 {
            bottom: -10%; left: 30%;
            width: 40vw; height: 40vw;
            background: radial-gradient(circle, rgba(236,72,153,0.18) 0%, transparent 70%);
            animation: aurora-3 28s ease-in-out infinite;
        }
        .aurora-4 {
            top: 50%; left: 25%;
            width: 35vw; height: 35vw;
            background: radial-gradient(circle, rgba(255,122,133,0.18) 0%, transparent 70%);
            animation: aurora-1 36s ease-in-out infinite reverse;
        }

        /* Top center beam */
        .aurora-beam {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80vw;
            height: 35vh;
            background: radial-gradient(ellipse at top, rgba(232,62,140,0.18) 0%, transparent 60%);
            pointer-events: none;
        }

        /* Fine dot grid */
        .dot-grid {
            position: fixed;
            inset: 0;
            background-image: radial-gradient(rgba(255,255,255,0.08) 1px, transparent 1px);
            background-size: 32px 32px;
            mask-image: radial-gradient(ellipse at center, black 30%, transparent 75%);
            -webkit-mask-image: radial-gradient(ellipse at center, black 30%, transparent 75%);
            pointer-events: none;
            z-index: 0;
            opacity: 0.5;
        }

        /* Noise texture */
        .noise-overlay {
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
            mix-blend-mode: overlay;
            pointer-events: none;
            z-index: 1;
            opacity: 0.5;
        }

        /* Top nav glow rim */
        nav.app-nav {
            border-bottom: 1px solid transparent;
            background-image:
                linear-gradient(135deg, rgba(255,255,255,0.06) 0%, rgba(255,255,255,0.02) 100%),
                linear-gradient(90deg, transparent 0%, rgba(232,62,140,0.4) 50%, transparent 100%);
            background-origin: border-box;
            background-clip: padding-box, border-box;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, rgba(232,62,140,0.4), rgba(99,102,241,0.3));
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover { background: linear-gradient(180deg, rgba(232,62,140,0.6), rgba(99,102,241,0.5)); }

        /* Inputs */
        input[type=range] {
            accent-color: #c82c75;
            height: 4px;
        }
        input[type=color] {
            cursor: pointer;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        input[type=color]::-webkit-color-swatch-wrapper { padding: 0; }
        input[type=color]::-webkit-color-swatch { border: 1px solid rgba(255,255,255,0.2); border-radius: 0.5rem; }

        /* Smooth focus ring on buttons */
        button:focus-visible, a:focus-visible {
            outline: 2px solid rgb(var(--emerald-glow) / 0.6);
            outline-offset: 2px;
            border-radius: 0.5rem;
        }

        /* Fade-in animation for content */
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fade-up 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) backwards; }

        /* Shimmer on dirty badge */
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .shimmer {
            background-image: linear-gradient(
                110deg,
                transparent 30%,
                rgba(251,191,36,0.3) 50%,
                transparent 70%
            );
            background-size: 200% 100%;
            animation: shimmer 2s linear infinite;
        }

        /* =====================================================================
         * Visual layout map (Phase 5) — phone-shaped diagram of the 11 slot
         * positions with filled/empty state. Click-to-focus the corresponding
         * slot card below.
         * ===================================================================== */
        .layout-map {
            position: relative;
            aspect-ratio: 9/16;
            max-width: 180px;
            margin: 0 auto;
            border: 2px solid rgba(255,255,255,0.12);
            border-radius: 18px;
            background:
                linear-gradient(135deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0.01) 100%),
                radial-gradient(circle at top, rgba(232,62,140,0.06) 0%, transparent 60%);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.06);
        }

        .lmap-zone {
            position: absolute;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.18);
            color: rgba(255,255,255,0.5);
            cursor: pointer;
            padding: 0;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .lmap-zone:hover { transform: scale(1.15); border-color: rgba(255,255,255,0.4); z-index: 5; }
        .lmap-zone.is-filled {
            background: linear-gradient(135deg, #c82c75 0%, #e83e8c 100%);
            border-color: rgba(232,62,140,0.5);
            color: white;
            box-shadow: 0 0 10px rgba(232,62,140,0.4);
        }
        .lmap-zone.is-focused {
            outline: 2px solid rgb(16,185,129);
            outline-offset: 2px;
            z-index: 6;
        }

        .lmap-zone--corner-tl { top: 6px; left: 6px;     width: 14px; height: 14px; border-radius: 999px; }
        .lmap-zone--corner-tr { top: 6px; right: 6px;    width: 14px; height: 14px; border-radius: 999px; }
        .lmap-zone--corner-bl { bottom: 6px; left: 6px;  width: 14px; height: 14px; border-radius: 999px; }
        .lmap-zone--corner-br { bottom: 6px; right: 6px; width: 14px; height: 14px; border-radius: 999px; }

        .lmap-zone--edge-top {
            top: 0; left: 30%; right: 30%; height: 7px;
            border-radius: 0 0 6px 6px; border-top: 0;
        }
        .lmap-zone--edge-bottom {
            bottom: 0; left: 30%; right: 30%; height: 7px;
            border-radius: 6px 6px 0 0; border-bottom: 0;
        }

        .lmap-zone--side-tc { top: 22px;    left: 50%; translate: -50% 0; width: 14px; height: 14px; border-radius: 999px; }
        .lmap-zone--side-bc { bottom: 22px; left: 50%; translate: -50% 0; width: 14px; height: 14px; border-radius: 999px; }
        .lmap-zone--side-lc { top: 50%; left: 6px;  translate: 0 -50%;    width: 14px; height: 14px; border-radius: 999px; }
        .lmap-zone--side-rc { top: 50%; right: 6px; translate: 0 -50%;    width: 14px; height: 14px; border-radius: 999px; }

        .lmap-zone--middle {
            top: 50%; left: 50%; translate: -50% -50%;
            width: 28px; height: 28px; border-radius: 999px;
        }

        /* Slot card focused-flash highlight when clicked from map */
        @keyframes slot-focus-flash {
            0%   { background-color: rgba(232,62,140,0.15); }
            100% { background-color: transparent; }
        }
        .slot-row.is-flash { animation: slot-focus-flash 1.6s ease-out; }

        /* =====================================================================
         * Anim preview picker — 10 mini boxes with looping animation thumbnails.
         * ===================================================================== */
        .anim-pick-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 4px;
        }
        .anim-pick-box {
            position: relative;
            aspect-ratio: 1;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 8px;
            cursor: pointer;
            overflow: hidden;
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex; align-items: center; justify-content: center;
        }
        .anim-pick-box:hover {
            border-color: rgba(232,62,140,0.4);
            transform: translateY(-1px);
        }
        .anim-pick-box.is-active {
            border-color: rgb(16,185,129);
            background: rgba(232,62,140,0.08);
            box-shadow: 0 0 0 2px rgba(232,62,140,0.25);
        }
        .anim-pick-label {
            position: absolute;
            inset: auto 0 2px 0;
            font-size: 7px;
            text-align: center;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            line-height: 1;
        }
        .anim-pick-dot {
            width: 32%;
            height: 32%;
            border-radius: 999px;
            background: linear-gradient(135deg, #e83e8c 0%, #ff7a85 100%);
            box-shadow: 0 0 6px rgba(232,62,140,0.5);
        }
        .anim-pick-none {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            font-size: 9px; color: rgba(255,255,255,0.4); gap: 2px;
        }
        .anim-pick-none svg { width: 14px; height: 14px; opacity: 0.5; }

        /* Looping preview keyframes (different from render's lay-* — these
         * oscillate continuously so user sees the entrance pattern repeat). */
        .ap--fade-in    .anim-pick-dot { animation: ap-fade-in    1.6s ease-in-out infinite; }
        .ap--fade-down  .anim-pick-dot { animation: ap-fade-down  1.6s ease-in-out infinite; }
        .ap--fade-up    .anim-pick-dot { animation: ap-fade-up    1.6s ease-in-out infinite; }
        .ap--fade-left  .anim-pick-dot { animation: ap-fade-left  1.6s ease-in-out infinite; }
        .ap--fade-right .anim-pick-dot { animation: ap-fade-right 1.6s ease-in-out infinite; }
        .ap--slide-down .anim-pick-dot { animation: ap-slide-down 1.6s cubic-bezier(0.34, 1.56, 0.64, 1) infinite; }
        .ap--slide-up   .anim-pick-dot { animation: ap-slide-up   1.6s cubic-bezier(0.34, 1.56, 0.64, 1) infinite; }
        .ap--scale-in   .anim-pick-dot { animation: ap-scale-in   1.6s cubic-bezier(0.34, 1.56, 0.64, 1) infinite; }
        .ap--rotate-in  .anim-pick-dot { animation: ap-rotate-in  1.8s cubic-bezier(0.34, 1.56, 0.64, 1) infinite; }
        .ap--blur-in    .anim-pick-dot { animation: ap-blur-in    1.6s ease-in-out infinite; }

        @keyframes ap-fade-in    { 0%, 100% { opacity: 0.15; }                                    50% { opacity: 1; } }
        @keyframes ap-fade-down  { 0%, 100% { opacity: 0.15; transform: translateY(-90%); }       50% { opacity: 1; transform: translateY(0); } }
        @keyframes ap-fade-up    { 0%, 100% { opacity: 0.15; transform: translateY(90%); }        50% { opacity: 1; transform: translateY(0); } }
        @keyframes ap-fade-left  { 0%, 100% { opacity: 0.15; transform: translateX(90%); }        50% { opacity: 1; transform: translateX(0); } }
        @keyframes ap-fade-right { 0%, 100% { opacity: 0.15; transform: translateX(-90%); }       50% { opacity: 1; transform: translateX(0); } }
        @keyframes ap-slide-down { 0%, 100% { opacity: 0.1;  transform: translateY(-150%); }      50% { opacity: 1; transform: translateY(0); } }
        @keyframes ap-slide-up   { 0%, 100% { opacity: 0.1;  transform: translateY(150%); }       50% { opacity: 1; transform: translateY(0); } }
        @keyframes ap-scale-in   { 0%, 100% { opacity: 0.15; transform: scale(0.4); }             50% { opacity: 1; transform: scale(1); } }
        @keyframes ap-rotate-in  { 0%, 100% { opacity: 0.15; transform: rotate(-180deg) scale(0.4); } 50% { opacity: 1; transform: rotate(0) scale(1); } }
        @keyframes ap-blur-in    { 0%, 100% { opacity: 0.15; filter: blur(8px); }                 50% { opacity: 1; filter: blur(0); } }
    </style>
</head>
<body class="min-h-screen text-white overflow-x-hidden relative"
      style="background:
          radial-gradient(ellipse at top, #0a1628 0%, #050816 60%),
          linear-gradient(180deg, #050816 0%, #060814 100%);
          background-attachment: fixed;">

{{-- Aurora background layers --}}
<div class="fixed inset-0 pointer-events-none overflow-hidden z-0" aria-hidden="true">
    <div class="aurora-blob aurora-1"></div>
    <div class="aurora-blob aurora-2"></div>
    <div class="aurora-blob aurora-3"></div>
    <div class="aurora-blob aurora-4"></div>
    <div class="aurora-beam"></div>
</div>
<div class="dot-grid" aria-hidden="true"></div>
<div class="noise-overlay" aria-hidden="true"></div>

{{-- Nav --}}
<nav class="app-nav sticky top-0 z-50 px-6 py-3 flex items-center gap-5 backdrop-blur-2xl"
     style="background: rgba(5,8,22,0.6); border-bottom: 1px solid rgba(255,255,255,0.06);">

    {{-- Soft light beam under nav --}}
    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1/2 h-px"
         style="background: linear-gradient(90deg, transparent, rgba(232,62,140,0.5), transparent);"
         aria-hidden="true"></div>

    <a href="{{ route('admin.themes.index') }}"
       class="flex items-center gap-2.5 group">
        <span class="relative w-8 h-8 rounded-xl flex items-center justify-center text-white text-sm font-bold tracking-wide overflow-hidden"
              style="background: linear-gradient(135deg, #c82c75 0%, #e83e8c 100%);
                     box-shadow: 0 4px 16px -2px rgba(232,62,140,0.5), inset 0 1px 0 rgba(255,255,255,0.3);">
            M
            <span class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity"
                  style="background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,0.4) 50%, transparent 70%);"></span>
        </span>
        <div class="flex items-baseline gap-1.5">
            <span class="font-semibold tracking-tight text-white/95">Marrified</span>
            <span class="text-xs uppercase tracking-widest text-emerald-400/80 font-semibold">Studio</span>
        </div>
    </a>

    <div class="h-5 w-px bg-gradient-to-b from-transparent via-white/15 to-transparent"></div>

    <nav class="flex items-center gap-1">
        @auth
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.themes.index') }}"
                   class="px-3 py-1.5 text-sm text-white/60 hover:text-white hover:bg-white/8 rounded-lg transition-all">
                    Themes
                </a>
                <a href="{{ route('admin.invitations.index') }}"
                   class="px-3 py-1.5 text-sm text-white/60 hover:text-white hover:bg-white/8 rounded-lg transition-all">
                    Invitations
                </a>
                <a href="{{ route('admin.music.index') }}"
                   class="px-3 py-1.5 text-sm text-white/60 hover:text-white hover:bg-white/8 rounded-lg transition-all">
                    Music
                </a>
            @endif
        @endauth
    </nav>

    <div class="ml-auto flex items-center gap-2">
        {{-- Live status pill --}}
        <div class="hidden md:flex items-center gap-1.5 px-2.5 py-1 glass-subtle rounded-full">
            <span class="relative flex w-1.5 h-1.5">
                <span class="absolute inline-flex w-full h-full rounded-full opacity-75 animate-ping" style="background-color: #10b981 !important;"></span>
                <span class="relative inline-flex rounded-full w-1.5 h-1.5" style="background-color: #10b981 !important;"></span>
            </span>
            <span class="text-[10px] uppercase tracking-widest font-semibold" style="color: rgba(52,211,153,0.85) !important;">Live</span>
        </div>

        <a href="{{ route('public.invitation', 'raka-dewi') }}" target="_blank"
           class="flex items-center gap-1.5 px-3 py-1.5 text-xs text-white/40 hover:text-white/70 glass-sm rounded-lg transition-all">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            Demo
        </a>

        @auth
            <span class="hidden md:inline text-xs text-white/30 font-mono px-2">{{ auth()->user()->email }}</span>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs text-white/40 hover:text-red-300/80 glass-sm rounded-lg transition-all">
                    Logout
                </button>
            </form>
        @endauth
    </div>
</nav>

{{-- Session flash --}}
@if (session('flash_message'))
    <div x-data="{ show: true }" x-show="show" x-cloak x-transition
         x-init="setTimeout(() => show = false, 4000)"
         class="fixed top-16 right-4 z-50 px-4 py-3 rounded-xl text-sm font-medium shadow-2xl glass-strong
                {{ session('flash_type') === 'error' ? 'border-red-400/30 text-red-300' : 'border-emerald-400/30 text-emerald-300' }}">
        {{ session('flash_message') }}
    </div>
@endif

<main class="relative z-10 @yield('full-width', '') p-6 max-w-screen-2xl mx-auto fade-up">
    @yield('content')
</main>

@livewireScripts
</body>
</html>
