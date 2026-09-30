<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — Tamakoshi Monitor</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: #0f172a;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(37, 99, 235, 0.18), transparent 45%),
                radial-gradient(circle at 80% 80%, rgba(37, 99, 235, 0.10), transparent 40%);
            color: #0f172a;
        }

        .login-wrap {
            width: 100%;
            max-width: 400px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            color: #fff;
        }

        .brand-logo {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            letter-spacing: 0.5px;
        }

        .brand-name {
            font-size: 18px;
            font-weight: 700;
            line-height: 1.2;
        }

        .brand-sub {
            font-size: 13px;
            color: #94a3b8;
        }

        .card {
            background: #fff;
            border-radius: 14px;
            padding: 32px 28px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
        }

        .card h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .card .lead {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 24px;
        }

        .error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #dc2626;
            color: #991b1b;
            font-size: 13px;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .field {
            margin-bottom: 16px;
        }

        .field label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #334155;
            margin-bottom: 6px;
        }

        .field input[type="email"],
        .field input[type="password"],
        .field input[type="text"] {
            width: 100%;
            padding: 11px 12px;
            font-size: 14px;
            font-family: inherit;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #fff;
            color: #0f172a;
            transition: border-color .15s, box-shadow .15s;
        }

        .field input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
        }

        .pw-wrap {
            position: relative;
        }

        .pw-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            color: #2563eb;
            font-family: inherit;
        }

        .row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 4px 0 20px;
            font-size: 13px;
            color: #475569;
        }

        .row label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .row input[type="checkbox"] {
            accent-color: #2563eb;
            width: 15px;
            height: 15px;
        }

        .btn {
            width: 100%;
            padding: 12px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            color: #fff;
            background: #2563eb;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background .15s, transform .05s;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .btn:active {
            transform: translateY(1px);
        }

        .foot {
            margin-top: 18px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }

        @media (max-width: 440px) {
            .card {
                padding: 26px 20px;
            }
        }
    </style>
</head>

<body>
    <div class="login-wrap">
        <div class="brand">
            <div class="brand-logo">TK</div>
            <div>
                <div class="brand-name">Tamakoshi Monitor</div>
                <div class="brand-sub">UT-1 Risk Intelligence</div>
            </div>
        </div>

        <div class="card">
            <h1>Sign in</h1>
            <p class="lead">Access the risk monitoring dashboard.</p>

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        autocomplete="username">
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                        <button type="button" class="pw-toggle" id="pwToggle">Show</button>
                    </div>
                </div>

                <div class="row">
                    <label><input type="checkbox" name="remember"> Remember me</label>
                </div>

                <button type="submit" class="btn">Sign in</button>
            </form>
        </div>

        <div class="foot">Authorized users only</div>
    </div>

    <script>
        const pw = document.getElementById('password');
        const toggle = document.getElementById('pwToggle');
        toggle.addEventListener('click', function() {
            const show = pw.type === 'password';
            pw.type = show ? 'text' : 'password';
            toggle.textContent = show ? 'Hide' : 'Show';
        });
    </script>
</body>

</html>
