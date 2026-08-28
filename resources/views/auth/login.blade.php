<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ $school->name ?? 'Taaluma SMS' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Taaluma brand — green, gold, white */
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
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: linear-gradient(160deg, var(--brand-green-darker) 0%, var(--brand-green) 100%);
        }

        .login-card {
            width: 100%;
            max-width: 340px;
            border-radius: .9rem;
            background: #fff;
            border-top: 4px solid var(--brand-gold);
            box-shadow: 0 16px 40px rgba(1, 15, 13, .35);
            padding: 2rem 1.75rem;
        }
        .brand-mark {
            width: 46px; height: 46px;
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, var(--brand-green), var(--brand-green-dark));
            color: var(--brand-gold-light);
            font-size: 1.3rem;
            margin-bottom: .6rem;
        }
        .school-logo {
            width: 46px; height: 46px; object-fit: cover;
            border-radius: 50%;
            margin-bottom: .6rem;
            border: 2px solid var(--brand-gold);
        }
        .login-card h5 { font-weight: 700; color: var(--brand-green-dark); margin-bottom: .1rem; }
        .login-card small.subtitle { color: #7a8683; }

        .form-label { font-size: .82rem; font-weight: 600; color: var(--brand-green-dark); }
        .form-control {
            border-radius: .55rem;
            padding: .55rem .8rem;
            border-color: #dfe6e2;
            font-size: .92rem;
        }
        .form-control:focus {
            border-color: var(--brand-green);
            box-shadow: 0 0 0 .18rem rgba(1, 38, 34, .12);
        }
        .btn-brand {
            background: linear-gradient(90deg, var(--brand-green), var(--brand-green-dark));
            border: none;
            color: var(--brand-gold-light);
            font-weight: 600;
            border-radius: .55rem;
            padding: .55rem;
            font-size: .92rem;
        }
        .btn-brand:hover { background: var(--brand-green-darker); color: var(--brand-gold-light); }

        .school-picker { margin-top: 1rem; }
        .school-picker .form-select { border-radius: .55rem; font-size: .85rem; border-color: #dfe6e2; }
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
                    <span class="brand-mark"><i class="bi bi-mortarboard-fill"></i></span>
                @endif
                <h5>{{ $school->name }}</h5>
                <small class="subtitle">Sign in to your account</small>
            @else
                <span class="brand-mark"><i class="bi bi-mortarboard-fill"></i></span>
                <h5>Taaluma SMS</h5>
                <small class="subtitle">Sign in to continue</small>
            @endif
        </div>

        @if($suspended ?? false)
            <div class="alert alert-warning small mb-0">
                <strong>{{ $school->name }}</strong>'s account is currently suspended. Please contact the platform administrator.
            </div>
        @else
            @if($errors->any())
                <div class="alert alert-danger small py-2">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ $school ? route('login.school', $school) : route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Username or Email</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username') }}" required autofocus autocapitalize="none" autocorrect="off">
                    <div class="form-text">Students: use your admission number.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small" for="remember">Remember me</label>
                </div>
                <button class="btn btn-brand w-100" type="submit">Login</button>
            </form>

            @if($school)
                <div class="text-center mt-3">
                    <a href="{{ route('login') }}" class="switch-link"><i class="bi bi-arrow-left"></i> Not {{ $school->name }}?</a>
                </div>
            @else
                @php $allSchools = \App\Models\School::where('is_active', true)->orderBy('name')->get(['name', 'slug']); @endphp
                @if($allSchools->isNotEmpty())
                    <div class="school-picker">
                        <label class="form-label mb-1"><i class="bi bi-search"></i> Find your school</label>
                        <select class="form-select form-select-sm" onchange="if(this.value) window.location = '/school/' + this.value + '/login';">
                            <option value="">Select your school…</option>
                            @foreach($allSchools as $s)
                                <option value="{{ $s->slug }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            @endif
        @endif
    </div>
</body>
</html>
