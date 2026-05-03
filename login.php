<?php
ob_start();
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $user_input = $_POST['username'];
    $pass_input = $_POST['password'];

    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$user_input'");
    $user_data = mysqli_fetch_assoc($result);

    if ($user_data) {
        if ($pass_input === $user_data['password']) {
            $_SESSION['id_user'] = $user_data['id_user'];
            $_SESSION['nama'] = $user_data['nama_lengkap'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Password yang Anda masukkan salah.";
        }
    } else {
        $error = "Username tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --cream: #F5F0E8;
            --warm-white: #FEFCF8;
            --ink: #1C1917;
            --ink-muted: #57534E;
            --ink-light: #A8A29E;
            --gold: #B45309;
            --gold-light: #FEF3C7;
            --gold-border: #D97706;
            --error-bg: #FFF1F2;
            --error-border: #FDA4AF;
            --error-text: #9F1239;
            --border: #E7E5E4;
            --shadow: 0 1px 3px rgba(28,25,23,0.08), 0 8px 32px rgba(28,25,23,0.06);
        }

        body {
            min-height: 100vh;
            background-color: var(--cream);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'DM Sans', sans-serif;
            background-image:
                radial-gradient(ellipse at 20% 20%, rgba(180,83,9,0.06) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(120,113,108,0.08) 0%, transparent 50%);
            padding: 2rem;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .brand {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .brand-icon {
            width: 52px;
            height: 52px;
            background: var(--ink);
            border-radius: 14px;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: rotate(-3deg);
        }

        .brand-icon svg {
            width: 26px;
            height: 26px;
            fill: none;
            stroke: var(--cream);
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .brand h1 {
            font-family: 'DM Serif Display', serif;
            font-size: 1.75rem;
            color: var(--ink);
            letter-spacing: -0.5px;
        }

        .brand p {
            font-size: 0.875rem;
            color: var(--ink-muted);
            margin-top: 0.35rem;
            font-weight: 300;
        }

        .card {
            background: var(--warm-white);
            border-radius: 20px;
            padding: 2.25rem 2.25rem 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .error-box {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: var(--error-bg);
            border: 1px solid var(--error-border);
            border-radius: 10px;
            padding: 0.85rem 1rem;
            margin-bottom: 1.5rem;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .error-icon {
            flex-shrink: 0;
            width: 18px;
            height: 18px;
            fill: none;
            stroke: var(--error-text);
            stroke-width: 2;
            stroke-linecap: round;
            margin-top: 1px;
        }

        .error-box span {
            font-size: 0.8rem;
            color: var(--error-text);
            line-height: 1.5;
        }

        .field {
            margin-bottom: 1.25rem;
        }

        .field label {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--ink-muted);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap .icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 17px;
            height: 17px;
            fill: none;
            stroke: var(--ink-light);
            stroke-width: 1.75;
            stroke-linecap: round;
            stroke-linejoin: round;
            pointer-events: none;
            transition: stroke 0.2s;
        }

        .input-wrap input {
            width: 100%;
            height: 46px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 0 14px 0 42px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            color: var(--ink);
            background: #FEFCF8;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
            -webkit-appearance: none;
        }

        .input-wrap input::placeholder {
            color: var(--ink-light);
        }

        .input-wrap input:focus {
            border-color: var(--gold-border);
            box-shadow: 0 0 0 3px rgba(217,119,6,0.12);
        }

        .input-wrap input:focus + .icon,
        .input-wrap:focus-within .icon {
            stroke: var(--gold-border);
        }

        .toggle-pass {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
        }

        .toggle-pass svg {
            width: 17px;
            height: 17px;
            fill: none;
            stroke: var(--ink-light);
            stroke-width: 1.75;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .toggle-pass:hover svg { stroke: var(--ink-muted); }

        .btn-login {
            width: 100%;
            height: 48px;
            background: var(--ink);
            color: var(--cream);
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 0.3px;
            cursor: pointer;
            margin-top: 0.5rem;
            transition: background 0.2s, transform 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            background: #2C2924;
        }

        .btn-login:active {
            transform: scale(0.985);
        }

        .btn-login svg {
            width: 16px;
            height: 16px;
            fill: none;
            stroke: var(--cream);
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .footer-text {
            text-align: center;
            margin-top: 1.75rem;
            font-size: 0.78rem;
            color: var(--ink-light);
        }
    </style>
</head>
<body>

<div class="login-wrapper">

    <div class="brand">
        <div class="brand-icon">
            <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        </div>
        <h1>Selamat Datang</h1>
        <p>Masuk ke akun Anda untuk melanjutkan</p>
    </div>

    <div class="card">

        <?php if (isset($error)): ?>
        <div class="error-box">
            <svg class="error-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
        <?php endif; ?>

        <form method="post" autocomplete="off">

            <div class="field">
                <label for="username">Username</label>
                <div class="input-wrap">
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Masukkan username"
                        value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                        required
                    >
                    <svg class="icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        required
                    >
                    <svg class="icon" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <button type="button" class="toggle-pass" onclick="togglePassword()" aria-label="Lihat password">
                        <svg id="eye-icon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <button type="submit" name="login" class="btn-login">
                Masuk
                <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>

        </form>

    </div>

    <p class="footer-text">&copy; <?= date('Y') ?> Portal. Semua hak dilindungi.</p>

</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eye-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}
</script>

</body>
</html>