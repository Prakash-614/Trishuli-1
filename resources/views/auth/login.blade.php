<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Tamakoshi Monitoring</title>

    <!-- Google Fonts & Bootstrap -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@500&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        :root {
            --font-sans: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        body {
            font-family: var(--font-sans);
            background: #0b0f19;
            background-image:
                radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.18) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(15, 23, 42, 0.6) 0px, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            margin: 0;
        }

        .login-box {
            max-width: 420px;
            width: 100%;
        }

        .auth-card {
            background: #ffffff;
            border: 1px solid #1e293b;
            border-radius: 16px;
            padding: 36px 32px;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 16px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }

        .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #475569;
            margin-bottom: 6px;
        }

        .input-group-text {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #94a3b8;
        }

        .form-control {
            font-size: 0.9rem;
            padding: 10px 12px;
            border-color: #cbd5e1;
            border-radius: 8px;
        }

        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .btn-signin {
            background: #2563eb;
            border: none;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 11px;
            border-radius: 8px;
            transition: all 0.15s ease;
        }

        .btn-signin:hover {
            background: #1d4ed8;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
        }

        .live-dot {
            width: 7px;
            height: 7px;
            background-color: #10b981;
            border-radius: 50%;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="login-box">
        <div class="text-center mb-3">
            <div class="d-inline-flex align-items-center gap-2 text-slate-400 small"
                style="color: #94a3b8; font-size: 0.8rem;">
                <span class="live-dot"></span>
                <span>Upper Trishuli-1 (216 MW) System</span>
            </div>
        </div>

        <div class="auth-card">
            <div class="brand-icon">TK</div>
            <h4 class="fw-bold text-dark mb-1" style="letter-spacing: -0.01em;">Tamakoshi Monitor</h4>
            <p class="text-muted small mb-4">Sign in to access real-time social & media risk intelligence</p>

            @if ($errors->any())
                <div
                    class="alert alert-danger py-2.5 px-3 rounded-3 small mb-4 d-flex align-items-center gap-2 border-0 bg-danger-subtle text-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Work Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                            placeholder="name@company.com" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label text-muted small" for="remember">Remember me</label>
                    </div>
                </div>

                <button type="submit"
                    class="btn btn-signin w-100 d-flex align-items-center justify-content-center gap-2">
                    <span>Sign In to Dashboard</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </div>

        <div class="text-center mt-4">
            <span class="text-slate-500 small" style="color: #64748b; font-size: 0.78rem;">
                Authorized personnel only · UT-1 & Sinohydro Communications
            </span>
        </div>
    </div>
</body>

</html>
