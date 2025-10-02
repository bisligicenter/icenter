<?php
require_once 'db.php';

$step = 1;
$success = '';
$error = '';
$email = '';
$code = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $code = trim($_POST['code']);
    $password = $_POST['password'] ?? '';

    if (isset($_POST['verify_code'])) {
        // Step 1: Verify code
        if (empty($email) || empty($code)) {
            $error = 'Please enter your email and the code.';
        } else {
            $stmt = $conn->prepare('SELECT * FROM users WHERE email = :email AND reset_token = :token AND reset_token_expires > NOW()');
            $stmt->execute([':email' => $email, ':token' => $code]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $step = 2; // Show password reset form
            } else {
                $error = 'Invalid or expired code.';
            }
        }
    } elseif (isset($_POST['reset_password'])) {
        // Step 2: Set new password
        if (empty($password)) {
            $error = 'Please enter a new password.';
            $step = 2;
        } else {
            $stmt = $conn->prepare('SELECT * FROM users WHERE email = :email AND reset_token = :token AND reset_token_expires > NOW()');
            $stmt->execute([':email' => $email, ':token' => $code]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE users SET password = :password, reset_token = NULL, reset_token_expires = NULL WHERE id = :id');
                $stmt->execute([
                    ':password' => $hashed,
                    ':id' => $user['id']
                ]);
                $success = 'Your password has been reset! <a href="login.php">Log in</a>';
                $step = 3;
            } else {
                $error = 'Invalid or expired code.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Reset Code</title>
    <link rel="icon" type="image/png" href="images/iCenter.png">
    <link rel="shortcut icon" type="image/png" href="images/iCenter.png">
    <link rel="apple-touch-icon" href="images/iCenter.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }
        p {
            color: white;
        }
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            background: linear-gradient(-45deg, rgb(0, 0, 0), rgb(0, 0, 0), #16213e, #0f3460);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .main-container {
            background: rgba(20, 20, 20, 0.32);
            border-radius: 32px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
            padding: 40px 32px;
            display: flex;
            flex-direction: row;
            gap: 24px;
            align-items: center;
            justify-content: center;
            max-width: 900px;
            margin: 48px auto;
            width: 100%;
            position: relative;
            z-index: 2;
            border: 1.5px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(6px);
        }
        @media (max-width: 991px) {
            .main-container {
                flex-direction: column;
                padding: 24px 8px;
                gap: 16px;
                max-width: 98vw;
            }
        }
        .logo-container {
            flex: 0 0 400px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            padding: 32px 20px 32px 20px;
            color: white;
            margin: 0;
            position: relative;
            min-height: 380px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .logo-container img {
            max-width: 100%;
            height: auto;
            max-height: 220px;
            min-height: 120px;
            margin-bottom: 28px;
            border: 2px solid white;
            border-radius: 30px;
            position: relative;
            z-index: 1;
        }
        .logo-container h1 {
            font-size: 2.8rem;
            margin-bottom: 18px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.7);
            font-weight: 700;
            letter-spacing: 1px;
        }
        .logo-container p {
            font-size: 1.5rem;
            text-shadow: 0 1px 5px rgba(0, 0, 0, 0.5);
            font-weight: 500;
            margin-bottom: 0;
        }
        .login-container {
            background: transparent;
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            text-align: center;
            width: 90%;
            max-width: 400px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transform: translateY(0);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        .login-container h2 {
            font-size: 2em;
            color: #ffffff;
            margin-top: 0;
        }
        .input-container {
            position: relative;
            margin: 20px auto;
            width: 90%;
            max-width: 350px;
        }
        .input-container input {
            width: 100%;
            padding: 15px 40px 15px 45px;
            margin: 0 auto;
            border: 1px solid #ccc;
            border-radius: 20px;
            font-size: 1rem;
            background-color: #333333;
            color: #ffffff;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        .input-container input:focus {
            outline: none;
            border-color: #0ad82a;
            background-color: #444444;
        }
        .input-container label {
            position: absolute;
            top: 50%;
            left: 45px;
            transform: translateY(-50%);
            font-size: 1rem;
            color: #aaaaaa;
            pointer-events: none;
            transition: all 0.3s ease;
        }
        .input-container input:focus + .input-label,
        .input-container input:not(:placeholder-shown) + .input-label {
            top: -10px;
            left: 45px;
            font-size: 0.85rem;
            color: #0ad82a;
            background-color: #121212;
            padding: 0 5px;
        }
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2em;
            color: #aaaaaa;
            pointer-events: none;
            transition: color 0.3s ease;
        }
        .input-container input:focus ~ .input-icon {
            color:rgb(148, 146, 146);
        }
        button {
            padding: 15px;
            background: black;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            width: 100%;
            font-size: 1.2em;
            margin-top: 20px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        button:hover {
            background: gray;
            box-shadow: 0 5px 15px rgb(0, 0, 0);
        }
        button:active {
            transform: scale(0.98);
        }
        button::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                to right,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }
        button:hover::after {
            transform: translateX(100%);
        }
        .error-message {
            background-color: rgba(255, 76, 76, 0.2);
            color: #ff4c4c;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #ff4c4c;
            animation: shake 0.5s;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        .error-message p {
            margin: 0;
        }
        .success-message {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        a {
            color: #0ad82a;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        /* Responsive Design - Mobile First Approach */
        /* Extra Large Devices (1200px and up) */
        @media (min-width: 1200px) {
            .main-container {
                width: 80%;
                max-width: 1400px;
            }
            .logo-container h1 {
                font-size: 3rem;
            }
            .logo-container p {
                font-size: 1.4rem;
            }
        }
        /* Large Devices (992px to 1199px) */
        @media (max-width: 1199px) and (min-width: 992px) {
            .main-container {
                width: 95%;
                max-width: 1100px;
            }
            .logo-container h1 {
                font-size: 2.8rem;
            }
            .logo-container p {
                font-size: 1.3rem;
            }
        }
        /* Medium Devices (768px to 991px) */
        @media (max-width: 991px) and (min-width: 768px) {
            .main-container {
                width: 95%;
                flex-direction: column;
                gap: 30px;
                padding: 20px;
            }
            .logo-container {
                flex: none;
                padding: 20px;
            }
            .logo-container img {
                max-height: 150px;
            }
            .logo-container h1 {
                font-size: 2.5rem;
            }
            .logo-container p {
                font-size: 1.2rem;
            }
            .login-container {
                flex: none;
                width: 100%;
                max-width: 500px;
                margin: 0 auto;
                border-radius: 20px;
            }
        }
        /* Small Devices (576px to 767px) */
        @media (max-width: 767px) and (min-width: 576px) {
            .main-container {
                width: 95%;
                flex-direction: column;
                gap: 20px;
                padding: 15px;
            }
            .logo-container {
                flex: none;
                padding: 15px;
            }
            .logo-container img {
                max-height: 120px;
            }
            .logo-container h1 {
                font-size: 2rem;
            }
            .logo-container p {
                font-size: 1rem;
            }
            .login-container {
                flex: none;
                width: 100%;
                max-width: 450px;
                margin: 0 auto;
                padding: 30px 25px;
                border-radius: 15px;
            }
            .login-container h2 {
                font-size: 1.8em;
            }
            .input-container {
                width: 95%;
                max-width: 320px;
            }
        }
        /* Extra Small Devices (up to 575px) */
        @media (max-width: 575px) {
            body {
                flex-direction: column;
                gap: 0;
                padding: 10px;
                align-items: stretch;
            }
            .logo-container {
                max-width: 100%;
                width: 100%;
                padding: 15px 10px;
            }
            .logo-container img {
                max-height: 70px;
                min-height: 40px;
            }
            .logo-container h1 {
                font-size: 1.2rem;
            }
            .logo-container p {
                font-size: 0.9rem;
            }
            .logo-container {
                padding-top: 32px;
            }
            .login-container {
                flex: none;
                width: 100%;
                max-width: 100%;
                margin: 0 auto;
                padding: 15px 5px;
                border-radius: 15px;
            }
            .login-container h2 {
                font-size: 1.2em;
            }
            .input-container {
                width: 100%;
                max-width: none;
                margin: 10px auto;
            }
            .input-container input {
                width: 100%;
                padding: 10px 35px 10px 40px;
                font-size: 0.95rem;
            }
            .input-container label {
                font-size: 0.9rem;
            }
            .input-container input:focus + .input-label,
            .input-container input:not(:placeholder-shown) + .input-label {
                font-size: 0.8rem;
            }
            .input-icon {
                font-size: 1em;
            }
            button {
                padding: 10px;
                font-size: 1em;
            }
            .error-message {
                padding: 10px;
                font-size: 0.9rem;
            }
        }
        /* Very Small Devices (up to 375px) */
        @media (max-width: 375px) {
            body {
                flex-direction: column;
                gap: 0;
                padding: 5px;
                align-items: stretch;
            }
            .logo-container {
                max-width: 100%;
                width: 100%;
                padding: 8px 2px;
            }
            .logo-container img {
                max-height: 60px;
            }
            .logo-container h1 {
                font-size: 1.1rem;
            }
            .login-container {
                padding: 8px 2px;
            }
            .login-container h2 {
                font-size: 1.4em;
            }
            .input-container input {
                padding: 10px 35px 10px 40px;
                font-size: 0.9rem;
            }
            .input-container label {
                font-size: 0.85rem;
            }
            .input-icon {
                font-size: 1em;
                left: 12px;
            }
            button {
                padding: 10px;
                font-size: 1em;
            }
            .error-message {
                padding: 10px;
                font-size: 0.9rem;
            }
        }
        /* Landscape orientation for mobile devices */
        @media (max-height: 500px) and (orientation: landscape) {
            body {
                flex-direction: row;
                gap: 10px;
                padding: 5px;
            }
            .logo-container, .login-container {
                flex: 1;
                padding: 10px 5px;
            }
            .logo-container img {
                max-height: 50px;
            }
        }
        /* High DPI displays */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .logo-container img {
                image-rendering: -webkit-optimize-contrast;
                image-rendering: crisp-edges;
            }
        }
        /* Reduced motion for accessibility */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="logo-container">
            <img src="images/iCenter.png" alt="Bislig iCenter Logo">
            <h1>Bislig iCenter</h1>
            <p>"No. 1 Supplier of iPhones in Mindanao"</p>
        </div>
        <div class="login-container">
            <h2>Reset Password with Code</h2>
            <?php if ($success): ?>
                <div class="success-message"> <?= $success ?> </div>
            <?php elseif ($error): ?>
                <div class="error-message"> <?= $error ?> </div>
            <?php endif; ?>
            <?php if ($step === 1): ?>
            <form method="post" action="">
                <div class="input-container">
                    <input type="email" name="email" id="email" placeholder=" " value="<?= htmlspecialchars($email) ?>" required>
                    <label for="email" class="input-label">Email</label>
                    <i class="fas fa-envelope input-icon"></i>
                </div>
                <div class="input-container">
                    <input type="text" name="code" id="code" placeholder=" " value="<?= htmlspecialchars($code) ?>" required>
                    <label for="code" class="input-label">Reset Code</label>
                    <i class="fas fa-key input-icon"></i>
                </div>
                <button type="submit" name="verify_code">Verify Code</button>
            </form>
            <?php elseif ($step === 2): ?>
            <form method="post" action="">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                <input type="hidden" name="code" value="<?= htmlspecialchars($code) ?>">
                <div class="input-container">
                    <input type="password" name="password" id="password" placeholder=" " required>
                    <label for="password" class="input-label">New Password</label>
                    <i class="fas fa-lock input-icon"></i>
                </div>
                <button type="submit" name="reset_password">Reset Password</button>
            </form>
            <?php endif; ?>
            <div style="margin-top: 10px;">
                <a href="login.php" style="color: #fff;">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html> 