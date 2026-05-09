<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Marrified Studio</title>
    @vite(['resources/css/app.css'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=playfair-display:400,500,600|space-grotesk:400,500,600,700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Space Grotesk', system-ui, sans-serif;
            background:
                radial-gradient(ellipse 80% 60% at 50% 0%, rgba(16,185,129,0.18), transparent 70%),
                radial-gradient(ellipse 60% 80% at 100% 100%, rgba(20,184,166,0.12), transparent 60%),
                #0a0f1a;
            min-height: 100vh;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .auth-card {
            width: 100%;
            max-width: 400px;
            background: linear-gradient(135deg, rgba(255,255,255,0.07), rgba(255,255,255,0.03));
            backdrop-filter: blur(20px) saturate(160%);
            -webkit-backdrop-filter: blur(20px) saturate(160%);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 1.5rem;
            padding: 2.5rem 2rem;
            box-shadow: 0 24px 60px -12px rgba(0,0,0,0.6);
        }
        .auth-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            font-weight: 600;
            background: linear-gradient(135deg, #10b981, #14b8a6);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin: 0 0 0.5rem;
            text-align: center;
        }
        .auth-subtitle {
            text-align: center;
            color: rgba(255,255,255,0.5);
            font-size: 0.85rem;
            margin: 0 0 2rem;
        }
        .auth-field { margin-bottom: 1rem; }
        .auth-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.5);
            margin-bottom: 0.5rem;
        }
        .auth-input {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 0.65rem;
            padding: 0.75rem 1rem;
            color: white;
            font-size: 0.9rem;
            transition: border-color 0.2s, background 0.2s;
        }
        .auth-input:focus {
            outline: none;
            border-color: rgba(16,185,129,0.5);
            background: rgba(255,255,255,0.08);
        }
        .auth-input.error { border-color: rgba(239,68,68,0.5); }
        .auth-error {
            color: #fca5a5;
            font-size: 0.78rem;
            margin-top: 0.4rem;
        }
        .auth-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255,255,255,0.7);
            font-size: 0.85rem;
            margin: 0.5rem 0 1.5rem;
            cursor: pointer;
        }
        .auth-checkbox input {
            width: 1rem;
            height: 1rem;
            accent-color: #10b981;
        }
        .auth-submit {
            width: 100%;
            padding: 0.85rem;
            background: linear-gradient(135deg, #10b981 0%, #14b8a6 100%);
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: 0.05em;
            border: 0;
            border-radius: 0.75rem;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(16,185,129,0.3);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .auth-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 28px rgba(16,185,129,0.4);
        }
        .auth-foot {
            text-align: center;
            color: rgba(255,255,255,0.3);
            font-size: 0.72rem;
            margin-top: 1.5rem;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <h1 class="auth-title">Marrified Studio</h1>
        <p class="auth-subtitle">Masuk untuk mengelola undangan</p>

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <div class="auth-field">
                <label class="auth-label" for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                       class="auth-input @error('email') error @enderror"
                       autocomplete="email" required autofocus>
                @error('email')
                    <p class="auth-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-field">
                <label class="auth-label" for="password">Password</label>
                <input id="password" name="password" type="password"
                       class="auth-input @error('password') error @enderror"
                       autocomplete="current-password" required>
                @error('password')
                    <p class="auth-error">{{ $message }}</p>
                @enderror
            </div>

            <label class="auth-checkbox">
                <input type="checkbox" name="remember" value="1">
                Ingat saya
            </label>

            <button type="submit" class="auth-submit">Masuk</button>
        </form>

        <p class="auth-foot">Akun couple di-generate oleh tim studio.</p>
    </div>
</body>
</html>
