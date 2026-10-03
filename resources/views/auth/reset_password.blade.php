<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — {{ $school->name ?? 'Taaluma SMS' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-green: #012622;
            --brand-green-dark: #01201D;
            --brand-green-darker: #010F0D;
            --brand-gold: #C9972F;
            --brand-gold-dark: #A97C1F;
            --brand-gold-light: #FBF1DC;
        }
        * { font-family: 'Poppins', sans-serif; }
        html, body { height: 100%; }
        body {
            margin: 0; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 1.5rem;
            background: linear-gradient(160deg, var(--brand-green-darker) 0%, var(--brand-green) 100%);
        }
        .login-card {
            width: 100%; max-width: 340px;
            border-radius: .9rem; background: #fff;
            border-top: 4px solid var(--brand-gold);
            box-shadow: 0 16px 40px rgba(1, 15, 13, .35);
            padding: 2rem 1.75rem;
        }
        .school-logo {
            width: 46px; height: 46px; object-fit: cover;
            border-radius: 50%; margin-bottom: .6rem;
            border: 2px solid var(--brand-gold);
        }
        .login-card h5 { font-weight: 700; color: var(--brand-green-dark); margin-bottom: .1rem; }
        .login-card small.subtitle { color: #7a8683; }
        .form-label { font-size: .82rem; font-weight: 600; color: var(--brand-green-dark); }
        .form-control {
            border-radius: .55rem; padding: .55rem .8rem;
            border-color: #dfe6e2; font-size: .92rem;
        }
        .form-control:focus {
            border-color: var(--brand-green);
            box-shadow: 0 0 0 .18rem rgba(1, 38, 34, .12);
        }
        .btn-brand {
            background: linear-gradient(90deg, var(--brand-green), var(--brand-green-dark));
            border: none; color: var(--brand-gold-light);
            font-weight: 600; border-radius: .55rem; padding: .55rem; font-size: .92rem;
        }
        .btn-brand:hover { background: var(--brand-green-darker); color: var(--brand-gold-light); }
        .switch-link { font-size: .8rem; color: var(--brand-green-dark); text-decoration: none; }
        .switch-link:hover { color: var(--brand-gold-dark); }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-3">
            @if($school)
                @if($school->logo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($school->logo_path) }}" alt="{{ $school->name }} logo" class="school-logo">
                @else
                    <img src="{{ asset('taaluma-logo.png') }}" alt="Taaluma" class="school-logo">
                @endif
                <h5>{{ $school->name }}</h5>
            @else
                <img src="{{ asset('taaluma-logo.png') }}" alt="Taaluma" class="school-logo">
                <h5>Taaluma SMS</h5>
            @endif
            <small class="subtitle">Set a new password</small>
        </div>

        @if($errors->any())
            <div class="alert alert-danger small py-2">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.reset.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="school_slug" value="{{ $school->slug ?? '' }}">

            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="{{ old('username', $username) }}" required autocapitalize="none" autocorrect="off">
            </div>
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" required minlength="8" autofocus>
                <div class="form-text">At least 8 characters. Only you will know this — not even your school admin.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-control" required minlength="8">
            </div>
            <button class="btn btn-brand w-100" type="submit">Set New Password</button>
        </form>

        <div class="text-center mt-3">
            <a href="{{ $school ? route('login.school', $school) : route('login') }}" class="switch-link"><i class="bi bi-arrow-left"></i> Back to login</a>
        </div>
    </div>
</body>
</html>
