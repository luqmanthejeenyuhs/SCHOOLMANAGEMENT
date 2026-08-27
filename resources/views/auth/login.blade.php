<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Taaluma SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-green: #0F7A45;
            --brand-green-dark: #0A5A33;
            --brand-green-light: #E8F5EC;
            --brand-green-mid: #2FA968;
            --brand-gold: #C9972F;
            --brand-gold-dark: #A97C1F;
            --brand-gold-light: #FBF1DC;
        }
        * { font-family: 'Poppins', sans-serif; }
        html, body { height: 100%; }
        body { margin: 0; }

        /* Full-page centered layout, replacing the old split screen */
        .auth-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            overflow: hidden;
            background: linear-gradient(160deg, var(--brand-green-dark) 0%, var(--brand-green) 45%, var(--brand-green-mid) 100%);
            background-size: 200% 200%;
            animation: gradientShift 16s ease infinite;
        }
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 0%; }
            50% { background-position: 100% 100%; }
        }

        /* Floating school-themed icons in the background, all around the centered card */
        .float-icon {
            position: absolute;
            color: rgba(255,255,255,.14);
            animation: drift 10s ease-in-out infinite;
            pointer-events: none;
        }
        .float-icon.i1 { top: 8%;  left: 10%; font-size: 4rem; animation-duration: 12s; }
        .float-icon.i2 { top: 70%; left: 8%;  font-size: 3rem; animation-duration: 9s; animation-delay: 1s; }
        .float-icon.i3 { top: 14%; left: 82%; font-size: 3.5rem; animation-duration: 11s; animation-delay: .5s; }
        .float-icon.i4 { top: 74%; left: 80%; font-size: 5rem; animation-duration: 14s; animation-delay: 2s; }
        .float-icon.i5 { top: 40%; left: 4%; font-size: 3rem; animation-duration: 15s; animation-delay: 1.2s; }
        .float-icon.i6 { top: 45%; left: 90%; font-size: 3.2rem; animation-duration: 13s; animation-delay: .8s; }
        @keyframes drift {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-22px) rotate(8deg); }
        }

        .brand-badge {
            display: inline-flex; align-items: center; gap: .5rem;
            background: var(--brand-gold-light);
            border: 1px solid var(--brand-gold);
            color: var(--brand-gold-dark);
            padding: .4rem .9rem; border-radius: 2rem;
            font-size: .8rem; font-weight: 600;
            margin-bottom: 1rem;
        }

        .login-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            border-radius: 1.2rem;
            border: none;
            border-top: 4px solid var(--brand-gold);
            box-shadow: 0 20px 60px rgba(10, 60, 35, .35);
            padding: 2.5rem 2.25rem;
            background: #fff;
        }
        .login-card .form-control {
            border-radius: .6rem;
            padding: .65rem .9rem;
            border-color: #dbe9df;
        }
        .login-card .form-control:focus {
            border-color: var(--brand-green);
            box-shadow: 0 0 0 .2rem rgba(15, 122, 69, .15);
        }
        .btn-brand {
            background: linear-gradient(90deg, var(--brand-green), var(--brand-green-dark));
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: .6rem;
            padding: .7rem;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .btn-brand:hover {
            box-shadow: 0 6px 16px rgba(15, 122, 69, .35);
            transform: translateY(-1px);
            color: #fff;
        }
        .demo-box {
            background: var(--brand-green-light);
            border-radius: .6rem;
            padding: .75rem .9rem;
            font-size: .8rem;
            color: var(--brand-green-dark);
        }
    </style>
</head>
<body>
<div class="auth-wrap">
    <i class="bi bi-mortarboard-fill float-icon i1"></i>
    <i class="bi bi-book-half float-icon i2"></i>
    <i class="bi bi-pencil-fill float-icon i3"></i>
    <i class="bi bi-backpack2-fill float-icon i4"></i>
    <i class="bi bi-award float-icon i5"></i>
    <i class="bi bi-calendar-week float-icon i6"></i>

    <div class="login-card">
        <div class="text-center mb-4">
            <span class="brand-badge"><i class="bi bi-shield-check"></i> Trusted School Platform</span>
            @if($school ?? null)
                <h3 class="mb-0" style="font-weight:800;color:var(--brand-green-dark);">
                    <i class="bi bi-mortarboard-fill" style="color:var(--brand-gold);"></i> {{ $school->name }}
                </h3>
                <small class="text-muted">Powered by Taaluma SMS — sign in to your account</small>
            @else
                <h3 class="mb-0" style="font-weight:800;color:var(--brand-green-dark);">
                    <i class="bi bi-mortarboard-fill" style="color:var(--brand-gold);"></i> Taaluma SMS
                </h3>
                <small class="text-muted">Sign in to your account to continue</small>
            @endif
        </div>

        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <button class="btn btn-brand w-100" type="submit">Login</button>
        </form>

        <hr>
        <div class="demo-box">
            <div class="fw-semibold mb-1"><i class="bi bi-info-circle"></i> Demo accounts (password: <code>password</code>)</div>
            <div>Admin — admin@school.test</div>
            <div>Teacher — teacher1@school.test</div>
            <div>Student — student1@school.test</div>
        </div>
    </div>
</div>
</body>
</html>
