@extends('layouts.user_type.guest')
@section('title', 'Sign In')

@section('content')
<style>
    body { background: #7491ad; }
    .ln-wrap {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
    }
    .ln-card {
        width: 100%;
        max-width: 980px;
        display: grid;
        grid-template-columns: 1.05fr 1fr;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(15, 23, 42, .25);
    }
    @media (max-width: 860px) {
        .ln-card { grid-template-columns: 1fr; }
        .ln-brand { display: none; }
    }
    .ln-brand {
        position: relative;
        padding: 44px 38px;
        color: #f1f5f9;
        background: linear-gradient(160deg, #0f172a 0%, #1e3a8a 100%);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
    }
    .ln-brand::before {
        content: '';
        position: absolute; inset: -40% -20% auto auto;
        width: 380px; height: 380px;
        background: radial-gradient(closest-side, rgba(255,255,255,.08), transparent);
        border-radius: 50%;
    }
    .ln-brand::after {
        content: '';
        position: absolute; inset: auto auto -30% -20%;
        width: 320px; height: 320px;
        background: radial-gradient(closest-side, rgba(255,255,255,.06), transparent);
        border-radius: 50%;
    }
    .ln-brand-logo {
        font-size: 1.05rem; font-weight: 700; letter-spacing: 2px;
        text-transform: uppercase; opacity: .85;
    }
    .ln-brand-title {
        font-size: 2.2rem; font-weight: 700; line-height: 1.15;
        margin: 18px 0 12px; position: relative; z-index: 1;
    }
    .ln-brand-sub {
        font-size: .95rem; opacity: .85; position: relative; z-index: 1;
        max-width: 340px;
    }
    .ln-brand-foot { font-size: .78rem; opacity: .65; position: relative; z-index: 1; }

    .ln-form-side {
        padding: 44px 40px;
        background: #ffffff;
        color: #1f2937;
    }
    .ln-h1 {
        font-size: 1.6rem; font-weight: 700; color: #111827; margin: 0 0 6px;
    }
    .ln-sub { color: #6b7280; font-size: .9rem; margin-bottom: 28px; }

    .ln-field { margin-bottom: 16px; }
    .ln-field label {
        display: block; font-size: .72rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: .6px;
        color: #4b5563; margin-bottom: 6px;
    }
    .ln-input-wrap { position: relative; }
    .ln-input-wrap .ln-icon {
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        color: #9ca3af; pointer-events: none;
    }
    .ln-input {
        width: 100%;
        padding: 12px 14px 12px 42px;
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        color: #111827;
        font-size: .92rem;
        transition: border-color .15s, box-shadow .15s;
    }
    .ln-input::placeholder { color: #9ca3af; }
    .ln-input:focus {
        outline: none;
        border-color: #1e3a8a;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(30, 58, 138, .15);
    }
    .ln-toggle {
        position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
        background: transparent; border: none; color: #6b7280;
        font-size: .8rem; cursor: pointer; padding: 4px 8px; border-radius: 4px;
    }
    .ln-toggle:hover { color: #111827; background: #f3f4f6; }

    .ln-row {
        display: flex; align-items: center; justify-content: space-between;
        margin: 6px 0 18px;
    }
    .ln-row label { color: #4b5563; font-size: .82rem; display: flex; align-items: center; gap: 8px; }
    .ln-row a { color: #1e3a8a; text-decoration: none; font-size: .82rem; font-weight: 600; }
    .ln-row a:hover { color: #0f172a; text-decoration: underline; }

    .ln-btn {
        width: 100%;
        padding: 12px 16px;
        border: none; border-radius: 6px;
        font-size: .95rem; font-weight: 600;
        color: #fff; cursor: pointer;
        background: #1e3a8a;
        transition: background .15s, transform .05s;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .10);
    }
    .ln-btn:hover { background: #1e40af; }
    .ln-btn:active { transform: translateY(1px); }

    .ln-alert {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        padding: 10px 12px; border-radius: 6px;
        font-size: .82rem; margin-bottom: 14px;
    }
    .ln-err { color: #b91c1c; font-size: .78rem; margin-top: 6px; }

    .ln-footer { text-align: center; margin-top: 20px; color: #6b7280; font-size: .82rem; }
    .ln-footer a { color: #1e3a8a; text-decoration: none; font-weight: 600; }
    .ln-footer a:hover { color: #0f172a; text-decoration: underline; }
</style>

<main class="main-content mt-0">
    <div class="ln-wrap">
        <div class="ln-card">
            {{-- Panel kiri: branding --}}
            <aside class="ln-brand">
                <div class="ln-brand-logo">Dashboard Management
                <div>
                    <div class="ln-brand-title">Selamat datang kembali</div>
                    <p class="ln-brand-sub">
                        Real-time monitoring Last Mile, Warehouse analysis, dan optimalkan kinerja dengan mudah.
                    </p>
                </div>
                <div class="ln-brand-foot">© {{ date('Y') }} HGS — Internal Use Only</div>
            </aside>

            {{-- Panel kanan: form --}}
            <section class="ln-form-side">
                <h1 class="ln-h1">Sign in</h1>
                <p class="ln-sub">Masuk dengan akun email Anda untuk melanjutkan.</p>

                @if (session('success'))
                    <div class="ln-alert">{{ session('success') }}</div>
                @endif

                <form role="form" method="POST" action="/session">
                    @csrf

                    <div class="ln-field">
                        <label for="email">Email</label>
                        <div class="ln-input-wrap">
                            <span class="ln-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                            </span>
                            <input type="email" id="email" name="email"
                                   class="ln-input" placeholder="nama@perusahaan.com"
                                   value="{{ old('email') }}" autocomplete="username" required>
                        </div>
                        @error('email')<div class="ln-err">{{ $message }}</div>@enderror
                    </div>

                    <div class="ln-field">
                        <label for="password">Password</label>
                        <div class="ln-input-wrap">
                            <span class="ln-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                            <input type="password" id="password" name="password"
                                   class="ln-input" placeholder="••••••••"
                                   autocomplete="current-password" required>
                            <button type="button" class="ln-toggle" id="lnTogglePwd" aria-label="Show password">Show</button>
                        </div>
                        @error('password')<div class="ln-err">{{ $message }}</div>@enderror
                    </div>

                    <div class="ln-row">
                        <label><input type="checkbox" name="remember"> Ingat saya</label>
                        <a href="/login/forgot-password">Lupa password?</a>
                    </div>

                    <button type="submit" class="ln-btn">Masuk</button>
                </form>

                <div class="ln-footer">
                    Belum punya akun? <a href="register">Daftar di sini</a>
                </div>
            </section>
        </div>
    </div>
</main>

<script>
    (function () {
        const btn = document.getElementById('lnTogglePwd');
        const inp = document.getElementById('password');
        if (!btn || !inp) return;
        btn.addEventListener('click', function () {
            const isPwd = inp.type === 'password';
            inp.type = isPwd ? 'text' : 'password';
            btn.textContent = isPwd ? 'Hide' : 'Show';
        });
    })();
</script>
@endsection
@include('harus_ada')