<?php
require_once 'db.php';

$success = '';
$error = '';

// Step control
$show_code_form = false;
$email_prefill = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && empty($code) && empty($password)) {
        // Step 1: Send code
        $stmt = $conn->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $reset_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', time() + 3600);
            $stmt = $conn->prepare('UPDATE users SET reset_token = :token, reset_token_expires = :expires WHERE email = :email');
            $stmt->execute([
                ':token' => $reset_code,
                ':expires' => $expires,
                ':email' => $email
            ]);
            // Send code by email
            $subject = "Password Reset Code";
            $message = "Your password reset code is: $reset_code\nThis code will expire in 1 hour.";
            $headers = "From: no-reply@" . $_SERVER['HTTP_HOST'];
            mail($email, $subject, $message, $headers);
            $success = "A reset code has been sent to your email. Please check your inbox.";
            $show_code_form = true;
            $email_prefill = $email;
        } else {
            $error = 'No account found with that email.';
        }
    } elseif (!empty($email) && !empty($code) && !empty($password)) {
        // Step 2: Verify code and reset password
        $stmt = $conn->prepare('SELECT * FROM users WHERE email = :email AND reset_token = :token AND reset_token_expires > NOW()');
        $stmt->execute([
            ':email' => $email,
            ':token' => $code
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE users SET password = :password, reset_token = NULL, reset_token_expires = NULL WHERE id = :id');
            $stmt->execute([
                ':password' => $hashed,
                ':id' => $user['id']
            ]);
            $success = 'Your password has been reset! <a href="login.php">Log in</a>';
            $show_code_form = false;
        } else {
            $error = 'Invalid or expired code.';
            $show_code_form = true;
            $email_prefill = $email;
        }
    } else {
        $error = 'Please enter your email.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="icon" type="image/png" href="images/iCenter.png">
    <link rel="shortcut icon" type="image/png" href="images/iCenter.png">
    <link rel="apple-touch-icon" href="images/iCenter.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- For icons -->
    <style>
        * { box-sizing: border-box; }
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
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
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
        /* Small Devices (max-width: 767px) */
        @media (max-width: 767px) {
            .main-container {
                width: 100vw;
                min-width: 0;
                padding: 10px 0;
                margin: 0;
                border-radius: 0;
                box-shadow: none;
            }
            .logo-container {
                min-height: 120px;
                padding: 12px 2px 12px 2px;
                max-width: 100vw;
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
            .login-container {
                width: 100vw;
                max-width: 100vw;
                min-width: 0;
                padding: 15px 5px;
                border-radius: 0;
                box-shadow: none;
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
                padding: 12px 35px 12px 40px;
                font-size: 1rem;
            }
            button {
                padding: 12px;
                font-size: 1em;
                border-radius: 10px;
            }
        }
        /* Extra Small Devices (max-width: 400px) */
        @media (max-width: 400px) {
            .logo-container h1 {
                font-size: 1rem;
            }
            .logo-container p {
                font-size: 0.7rem;
            }
            .login-container h2 {
                font-size: 1em;
            }
            .input-container input {
                font-size: 0.9rem;
            }
            button {
                font-size: 0.95em;
            }
        }
        /* Ensure touch targets are large enough */
        input, button, label {
            min-height: 44px;
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
        /* Move email label a little lower beside the icon */
        .input-container input[type='email'] + .input-label {
            top: 60%;
            transform: translateY(-40%);
        }
        /* Hide email label when input is focused */
        .input-container input[type='email']:focus + .input-label {
            opacity: 0;
            visibility: hidden;
        }
        /* Only animate label for non-email fields */
        .input-container input:not([type="email"]):focus + .input-label,
        .input-container input:not([type="email"]):not(:placeholder-shown) + .input-label {
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
        .success-message {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
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
        a {
            color: #0ad82a;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
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
            <h2>Reset Password</h2>
            <?php if ($success && !$show_code_form): ?>
                <div class="success-message"> <?= $success ?> </div>
            <?php elseif ($error): ?>
                <div class="error-message"> <?= $error ?> </div>
            <?php endif; ?>
            <?php if (!$success || $show_code_form): ?>
                <?php if (!$show_code_form): ?>
                <!-- Step 1: Enter email -->
                <form method="post" action="">
                    <div class="input-container">
                        <input type="email" name="email" id="email" placeholder=" " required>
                        <label for="email" class="input-label">Email</label>
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                    <button type="submit">Send Reset Code</button>
                </form>
                <?php else: ?>
                <!-- Step 2: Enter code and new password -->
                <form method="post" action="">
                    <div class="input-container">
                        <input type="email" name="email" id="email" placeholder=" " required value="<?= htmlspecialchars($email_prefill) ?>" readonly>
                        <label for="email" class="input-label">Email</label>
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                    <div class="input-container">
                        <input type="text" name="code" id="code" placeholder=" " required>
                        <label for="code" class="input-label">Reset Code</label>
                        <i class="fas fa-key input-icon"></i>
                    </div>
                    <div class="input-container">
                        <input type="password" name="password" id="password" placeholder=" " required>
                        <label for="password" class="input-label">New Password</label>
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                    <button type="submit">Reset Password</button>
                </form>
                <?php endif; ?>
            <?php endif; ?>
            <div style="margin-top: 10px;">
                <a href="login.php" style="color: #fff;">Back to Login</a>
            </div>
        </div>
    </div>
</body>
</html> 