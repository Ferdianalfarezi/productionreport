<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Production Report</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #020408;
            overflow: hidden;
        }

        /* ── BG ── */
        .bg {
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 120% 80% at 0% 0%, #0c1a3a 0%, transparent 55%),
                radial-gradient(ellipse 80% 80% at 100% 100%, #0a1528 0%, transparent 55%),
                #020408;
        }

        /* Glow blobs */
        .blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            pointer-events: none;
        }
        .blob-1 { width: 600px; height: 600px; background: #0d2a6b; top: -200px; left: -150px; opacity: 0.5; animation: drift1 20s ease-in-out infinite; }
        .blob-2 { width: 500px; height: 500px; background: #071a4a; bottom: -150px; right: -100px; opacity: 0.45; animation: drift2 25s ease-in-out infinite; }
        .blob-3 { width: 350px; height: 350px; background: #0f3070; top: 50%; left: 55%; opacity: 0.2; animation: drift1 18s ease-in-out infinite reverse; }

        @keyframes drift1 {
            0%,100% { transform: translate(0,0); }
            50% { transform: translate(40px,-50px); }
        }
        @keyframes drift2 {
            0%,100% { transform: translate(0,0); }
            50% { transform: translate(-30px,40px); }
        }

        /* ── WRAPPER ── */
        .wrap {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 460px;
            padding: 1.5rem;
            animation: fadeUp .6s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes fadeUp {
            from { opacity:0; transform: translateY(24px); }
            to   { opacity:1; transform: translateY(0); }
        }

        /* ── CARD ── */
        .card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow:
                0 40px 100px rgba(0,0,0,0.8),
                0 12px 32px rgba(0,0,0,0.5),
                0 0 0 1px rgba(255,255,255,0.05);
        }

        /* ── CARD HEADER ── */
        .card-header {
            background: linear-gradient(145deg, #0f172a 0%, #1a2744 50%, #0f172a 100%);
            padding: 2.25rem 2.5rem 2rem;
            position: relative;
            overflow: hidden;
        }
        .card-header::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 200px; height: 200px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59,130,246,0.2) 0%, transparent 70%);
        }
        .card-header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent 0%, rgba(96,165,250,0.4) 40%, rgba(59,130,246,0.6) 50%, rgba(96,165,250,0.4) 60%, transparent 100%);
        }

        .brand-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1rem;
            box-shadow: 0 6px 20px rgba(37,99,235,0.5);
        }
        .brand-icon svg { width: 24px; height: 24px; color: #fff; }

        .brand-title {
            font-size: 1.55rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.03em;
            line-height: 1;
            margin-bottom: 0.3rem;
            position: relative;
        }
        .brand-sub {
            font-size: 0.78rem;
            color: rgba(148,163,184,0.75);
            font-weight: 400;
            position: relative;
        }

        /* ── CARD BODY ── */
        .card-body {
            padding: 2rem 2.5rem 2rem;
            background: #fff;
        }

        /* Error */
        .error-alert {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: #fff1f2;
            border: 1px solid #fecdd3;
            border-left: 3px solid #f43f5e;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
        }
        .error-alert svg { width: 16px; height: 16px; color: #f43f5e; flex-shrink: 0; }
        .error-alert p { font-size: 0.82rem; color: #be123c; font-weight: 500; }

        /* Field */
        .field { margin-bottom: 1.2rem; }
        .field-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 600;
            color: #64748b;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.45rem;
        }
        .field-inner { position: relative; }
        .field-icon {
            position: absolute;
            left: 13px; top: 50%; transform: translateY(-50%);
            display: flex; color: #94a3b8;
            pointer-events: none;
        }
        .field-icon svg { width: 16px; height: 16px; }

        .field-input {
            width: 100%;
            padding: 0.78rem 1rem 0.78rem 2.5rem;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.9rem;
            color: #0f172a;
            background: #f8fafc;
            outline: none;
            transition: all 0.2s;
        }
        .field-input:focus {
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        }
        .field-input::placeholder { color: #cbd5e1; }

        .pwd-toggle {
            position: absolute;
            right: 11px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #94a3b8; padding: 4px; border-radius: 6px;
            display: flex; transition: color 0.15s;
        }
        .pwd-toggle:hover { color: #475569; }
        .pwd-toggle svg { width: 16px; height: 16px; }

        /* Remember */
        .remember {
            display: flex; align-items: center; gap: 0.6rem;
            margin-bottom: 1.5rem;
        }
        .remember input {
            width: 15px; height: 15px;
            accent-color: #1e40af;
            cursor: pointer;
        }
        .remember label {
            font-size: 0.83rem;
            color: #64748b;
            cursor: pointer;
        }

        /* Submit */
        .btn-login {
            width: 100%;
            padding: 0.88rem;
            background: #0f172a;
            color: #fff;
            border: none;
            border-radius: 11px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.93rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
        }
        .btn-login::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.07), transparent);
            transform: translateX(-100%);
            transition: transform 0.5s;
        }
        .btn-login:hover {
            background: #1e293b;
            transform: translateY(-1px);
            box-shadow: 0 10px 28px rgba(0,0,0,0.35);
        }
        .btn-login:hover::after { transform: translateX(100%); }
        .btn-login:active { transform: translateY(0); }

        /* Card footer */
        .card-footer {
            border-top: 1px solid #f1f5f9;
            padding: 1rem 2.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.35rem;
        }
        .copyright-text {
            font-size: 0.72rem;
            color: #94a3b8;
            text-align: center;
            letter-spacing: 0.02em;
        }
        .copyright-text strong { color: #64748b; font-weight: 600; }

        .brand-icon {
            width: 48px; height: 48px;
            background: transparent;  /* ganti dari gradient biru */
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1rem;
            box-shadow: none;  /* hapus shadow biru */
        }
    </style>
</head>
<body>

<div class="bg"></div>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="blob blob-3"></div>

<div class="wrap">
    <div class="card">
            <div class="card-header">
            <div style="display:flex; align-items:center; gap:1rem;">
                <img src="{{ asset('images/logostep.png') }}" alt="Logo"
                    style="width:90px; height:90px; object-fit:contain; flex-shrink:0;">
                <div>
                    <div class="brand-title">Production Report</div>
                    <div class="brand-sub">Management System </div>
                </div>
            </div>
        </div>

        <!-- Body -->
        <div class="card-body">

            @if($errors->any())
            <div class="error-alert">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p>{{ $errors->first() }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <!-- Username -->
                <div class="field">
                    <label class="field-label">Username</label>
                    <div class="field-inner">
                        <span class="field-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                        <input type="text" name="username" value="{{ old('username') }}"
                            required autofocus
                            class="field-input @error('username') error @enderror"
                            placeholder="Masukkan username">
                    </div>
                </div>

                <!-- Password -->
                <div class="field">
                    <label class="field-label">Password</label>
                    <div class="field-inner">
                        <span class="field-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <input type="password" id="pwdInput" name="password" required
                            class="field-input"
                            placeholder="Masukkan password">
                        <button type="button" class="pwd-toggle" onclick="togglePwd()">
                            <svg id="eyeShow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="eyeHide" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember -->
                <div class="remember">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ingat saya di perangkat ini</label>
                </div>

                <button type="submit" class="btn-login">Masuk ke Sistem</button>
            </form>
        </div>

        <!-- Card footer -->
        <div class="card-footer">
            <p class="copyright-text">© {{ date('Y') }} STEP <strong>IT Dept</strong> — All Rights Reserved</p>
        </div>

    </div>
</div>

<script>
function togglePwd() {
    const input = document.getElementById('pwdInput');
    const show  = document.getElementById('eyeShow');
    const hide  = document.getElementById('eyeHide');
    const isHidden = input.type === 'password';
    input.type     = isHidden ? 'text' : 'password';
    show.style.display = isHidden ? 'none' : '';
    hide.style.display = isHidden ? '' : 'none';
}
</script>
</body>
</html>