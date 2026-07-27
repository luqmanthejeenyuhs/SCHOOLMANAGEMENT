<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-blue: #1447e6;
            --brand-blue-dark: #0b2e8a;
            --brand-blue-light: #e8f0fe;
            --brand-blue-mid: #4d7cf5;
        }
        * { font-family: 'Poppins', sans-serif; }
        html, body { height: 100%; }
        body { margin: 0; }

        .auth-wrap { min-height: 100vh; display: flex; }

        /* Left brand panel */
        .brand-panel {
            flex: 1 1 50%;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--brand-blue-dark), var(--brand-blue) 55%, var(--brand-blue-mid));
            background-size: 200% 200%;
            animation: gradientShift 14s ease infinite;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
        }
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 0%; }
            50% { background-position: 100% 100%; }
        }
        .brand-panel h1 { font-weight: 800; font-size: 2.3rem; }
        .brand-panel p { color: rgba(255,255,255,.85); max-width: 420px; }

        /* Floating school-themed icons */
        .float-icon {
            position: absolute;
            color: rgba(255,255,255,.16);
            animation: drift 10s ease-in-out infinite;
        }
        .float-icon.i1 { top: 8%;  left: 10%; font-size: 4rem; animation-duration: 12s; }
        .float-icon.i2 { top: 65%; left: 6%;  font-size: 3rem; animation-duration: 9s; animation-delay: 1s; }
        .float-icon.i3 { top: 18%; left: 78%; font-size: 3.5rem; animation-duration: 11s; animation-delay: .5s; }
        .float-icon.i4 { top: 72%; left: 72%; font-size: 5rem; animation-duration: 14s; animation-delay: 2s; }
        .float-icon.i5 { top: 42%; left: 42%; font-size: 6.5rem; animation-duration: 16s; animation-delay: 1.5s; }
        @keyframes drift {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-22px) rotate(8deg); }
        }

        .brand-badge {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(255,255,255,.15);
            border: 1px solid rgba(255,255,255,.3);
            padding: .4rem .9rem; border-radius: 2rem;
            font-size: .85rem; font-weight: 600;
            margin-bottom: 1.25rem; width: fit-content;
        }

        /* Right form panel */
        .form-panel {
            flex: 1 1 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f7f9fd;
            padding: 2rem;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 1.1rem;
            border: none;
            box-shadow: 0 10px 40px rgba(20, 71, 230, .12);
            padding: 2.25rem;
            background: #fff;
        }
        .login-card .form-control {
            border-radius: .6rem;
            padding: .65rem .9rem;
            border-color: #dbe3f5;
        }
        .login-card .form-control:focus {
            border-color: var(--brand-blue);
            box-shadow: 0 0 0 .2rem rgba(20, 71, 230, .15);
        }
        .btn-brand {
            background: linear-gradient(90deg, var(--brand-blue), var(--brand-blue-dark));
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: .6rem;
            padding: .65rem;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .btn-brand:hover {
            box-shadow: 0 6px 16px rgba(20, 71, 230, .35);
            transform: translateY(-1px);
            color: #fff;
        }
        .demo-box {
            background: var(--brand-blue-light);
            border-radius: .6rem;
            padding: .75rem .9rem;
            font-size: .8rem;
            color: var(--brand-blue-dark);
        }

        @media (max-width: 860px) {
            .brand-panel { display: none; }
            .form-panel { flex: 1 1 100%; }
        }
    </style>
</head>
<body>
<div class="auth-wrap">
    <div class="brand-panel">
        <i class="bi bi-mortarboard-fill float-icon i1"></i>
        <i class="bi bi-book-half float-icon i2"></i>
        <i class="bi bi-pencil-fill float-icon i3"></i>
        <i class="bi bi-backpack2-fill float-icon i4"></i>
        <i class="bi bi-mortarboard float-icon i5"></i>

        <span class="brand-badge"><i class="bi bi-shield-check"></i> Trusted School Platform</span>
        <h1>School Management System</h1>
        <p>Manage students, teachers, classes, exams, fees and attendance — all from one place.</p>
    </div>

    <div class="form-panel">
        <div class="login-card">
            <div class="text-center mb-4">
                <i class="bi bi-mortarboard-fill" style="font-size:2.2rem;color:var(--brand-blue);"></i>
                <h4 class="mt-2 mb-0" style="font-weight:700;">Welcome back</h4>
                <small class="text-muted">Sign in to your account to continue</small>
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
</div>
</body>
</html>
