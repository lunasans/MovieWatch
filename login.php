<?php
require 'config/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $stayLoggedIn = isset($_POST['stay_logged_in']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];

        if ($stayLoggedIn) {
            $token = bin2hex(random_bytes(32));
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

            $stmt = $pdo->prepare("INSERT INTO user_tokens (user_id, token, ip_address, user_agent) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user['id'], $token, $ip, $agent]);

            setcookie('remember_token', $token, time() + (365 * 24 * 60 * 60), "/");
        }

        header('Location: index.php');
        exit;
    } else {
        $error = 'Ungültige Anmeldedaten';
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MovieWatch</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --background: #1a1a2e;
            --color: #ffffff;
            --primary-color: #0f3460;
            --accent-color: #3498db;
            
            --clr-text: var(--color);
            --clr-text-muted: rgba(255, 255, 255, 0.7);
            --clr-background: var(--background);
            --clr-primary: var(--primary-color);
            --clr-accent: var(--accent-color);
            --clr-surface: rgba(255, 255, 255, 0.1);
            --clr-border: rgba(255, 255, 255, 0.2);
            --clr-shadow: rgba(0, 0, 0, 0.3);
            
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-backdrop: blur(20px);
            
            --radius-md: 1rem;
            --radius-lg: 1.5rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            
            --transition-smooth: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--clr-background);
            color: var(--clr-text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .circle {
            position: fixed;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--clr-primary), var(--clr-accent));
            animation: ripple 15s infinite;
            z-index: -1;
        }

        .circle-one {
            height: 13rem;
            width: 13rem;
            top: -40px;
            right: -40px;
            animation-delay: 0s;
        }

        .circle-two {
            height: 22rem;
            width: 22rem;
            bottom: -120px;
            left: -120px;
            animation-delay: 7s;
        }

        @keyframes ripple {
            0% { transform: scale(0.8); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.7; }
            100% { transform: scale(0.8); opacity: 1; }
        }

        .login-container {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-backdrop);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 50px var(--clr-shadow);
            position: relative;
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: var(--spacing-xl);
            background: linear-gradient(135deg, var(--clr-accent), var(--clr-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
        }

        .form-group {
            margin-bottom: var(--spacing-lg);
        }

        .form-input {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg);
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-md);
            color: var(--clr-text);
            font-size: 1rem;
            transition: var(--transition-smooth);
            backdrop-filter: var(--glass-backdrop);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--clr-accent);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .form-input::placeholder {
            color: var(--clr-text-muted);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-lg);
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--clr-accent);
        }

        .checkbox-group label {
            font-size: 0.9rem;
            color: var(--clr-text-muted);
            cursor: pointer;
        }

        .login-btn {
            width: 100%;
            padding: var(--spacing-md) var(--spacing-lg);
            background: linear-gradient(135deg, var(--clr-accent), var(--clr-primary));
            border: none;
            border-radius: var(--radius-md);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-smooth);
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: var(--transition-smooth);
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px var(--clr-shadow);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .error-message {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
            border-radius: var(--radius-md);
            color: #e74c3c;
            padding: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
            text-align: center;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
        }

        .theme-switcher {
            position: fixed;
            bottom: var(--spacing-lg);
            right: var(--spacing-lg);
            display: flex;
            gap: var(--spacing-sm);
            z-index: 100;
        }

        .theme-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--glass-border);
            cursor: pointer;
            transition: var(--transition-smooth);
            backdrop-filter: var(--glass-backdrop);
        }

        .theme-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px var(--clr-shadow);
        }

        @media (max-width: 480px) {
            .login-container {
                margin: var(--spacing-md);
                padding: var(--spacing-lg);
            }
            
            .circle-one {
                height: 8rem;
                width: 8rem;
            }
            
            .circle-two {
                height: 15rem;
                width: 15rem;
            }
        }
    </style>
</head>
<body>
    <!-- Animierte Hintergrund-Kreise -->
    <div class="circle circle-one"></div>
    <div class="circle circle-two"></div>

    <div class="login-container">
        <h1 class="login-title">
            <i class="bi bi-film"></i>
            MovieWatch
        </h1>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="bi bi-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <input 
                    class="form-input" 
                    type="text" 
                    name="username" 
                    placeholder="Benutzername" 
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                    required
                    autocomplete="username"
                >
            </div>

            <div class="form-group">
                <input 
                    class="form-input" 
                    type="password" 
                    name="password" 
                    placeholder="Passwort" 
                    required
                    autocomplete="current-password"
                >
            </div>

            <div class="checkbox-group">
                <input type="checkbox" name="stay_logged_in" id="stayLoggedIn">
                <label for="stayLoggedIn">Angemeldet bleiben</label>
            </div>

            <button type="submit" class="login-btn">
                <i class="bi bi-box-arrow-in-right" style="margin-right: 0.5rem;"></i>
                Anmelden
            </button>
        </form>
    </div>

    <!-- Theme Switcher -->
    <div class="theme-switcher" id="themeSwitcher"></div>

    <script>
        // Theme System (wie im ursprünglichen Login)
        const themes = [
            { background: "#1a1a2e", color: "#ffffff", primaryColor: "#0f3460", accentColor: "#3498db" },
            { background: "#461220", color: "#ffffff", primaryColor: "#E94560", accentColor: "#ff6b8a" },
            { background: "#192A51", color: "#ffffff", primaryColor: "#967AA1", accentColor: "#c39bd3" },
            { background: "#2d1b69", color: "#ffffff", primaryColor: "#8e44ad", accentColor: "#9b59b6" },
            { background: "#0c5460", color: "#ffffff", primaryColor: "#16a085", accentColor: "#1abc9c" }
        ];

        const setTheme = (theme) => {
            const root = document.documentElement;
            Object.entries(theme).forEach(([key, value]) => {
                root.style.setProperty(`--${key.replace(/([A-Z])/g, '-$1').toLowerCase()}`, value);
            });
            localStorage.setItem('movieWatchTheme', JSON.stringify(theme));
        };

        const displayThemeButtons = () => {
            const btnContainer = document.getElementById("themeSwitcher");
            themes.forEach((theme, index) => {
                const div = document.createElement("div");
                div.className = "theme-btn";
                div.style.background = `linear-gradient(135deg, ${theme.primaryColor}, ${theme.accentColor})`;
                div.title = `Theme ${index + 1}`;
                btnContainer.appendChild(div);
                div.addEventListener("click", () => setTheme(theme));
            });
        };

        document.addEventListener('DOMContentLoaded', function() {
            displayThemeButtons();
            
            // Gespeichertes Theme laden
            const savedTheme = localStorage.getItem('movieWatchTheme');
            if (savedTheme) {
                setTheme(JSON.parse(savedTheme));
            }

            // Form-Enhancement
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const submitBtn = form.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise" style="animation: spin 1s linear infinite; margin-right: 0.5rem;"></i>Anmeldung...';
                
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-box-arrow-in-right" style="margin-right: 0.5rem;"></i>Anmelden';
                }, 10000);
            });
        });

        // Spin-Animation für Loading
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>