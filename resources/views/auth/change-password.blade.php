<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Your Password — Taaluma SMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-green: #0F7A45;
            --brand-green-dark: #0A5A33;
            --brand-green-mid: #2FA968;
            --brand-gold: #C9972F;
        }
        * { font-family: 'Poppins', sans-serif; }
        html, body { height: 100%; margin: 0; }
        .auth-wrap {
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 2rem 1rem;
            background: linear-gradient(160deg, var(--brand-green-dark) 0%, var(--brand-green) 45%, var(--brand-green-mid) 100%);
        }
        .login-card {
            background: #fff; border-radius: 1rem; padding: 2.25rem;
            width: 100%; max-width: 420px; box-shadow: 0 20px 60px rgba(0,0,0,.25);
        }
        .btn-brand {
            background: linear-gradient(90deg, var(--brand-green), var(--brand-green-dark));
            color: #fff; font-weight: 600; border-radius: .6rem; padding: .7rem;
        }
        .btn-brand:hover { color: #fff; }
    </style>
</head>
<body>
<div class="auth-wrap">
    <div class="login-card">
        <div class="text-center mb-4">
            <h3 style="font-weight:800;color:var(--brand-green-dark);"><i class="bi bi-shield-lock-fill" style="color:var(--brand-gold);"></i> Set Your Password</h3>
            <small class="text-muted">For your security, choose a new password only you know before continuing.</small>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" minlength="10" required autofocus>
                <div class="form-text">At least 10 characters.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-control" minlength="10" required>
            </div>
            <button class="btn btn-brand w-100" type="submit">Set Password &amp; Continue</button>
        </form>
    </div>
</div>
</body>
</html>
