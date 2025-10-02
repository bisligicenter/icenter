<?php
session_start(); // Start the session

// Check if the user is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: logout.php"); // Redirect to the main page if already logged in
    exit();
}

// Initialize login attempts if not set
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

// Check if the user is locked out
if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
    $error = "Too many login attempts. Please try again later.";
} else {
    // Handle login form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        require_once 'db.php';
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Check credentials in the users table
        $stmt = $conn->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'pending') {
                $error = 'Your account is pending approval by the admin.';
            } elseif ($user['status'] === 'rejected') {
                $error = 'Your account registration was rejected. Please contact admin.';
            } else {
                // User authenticated
                $_SESSION['login_attempts'] = 0; // Reset attempts on successful login
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                error_log('Login successful for user: ' . $user['username'] . ' with role: ' . $user['role']);
                if ($user['role'] === 'admin') {
                    $_SESSION['admin_logged_in'] = true;
                    error_log('Redirecting to admin.php for admin user: ' . $user['username']);
                    header('Location: admin.php');
                    exit();
                } elseif ($user['role'] === 'staff') {
                    $_SESSION['staff_logged_in'] = true;
                    error_log('Redirecting to staff.php for staff user: ' . $user['username']);
                    header('Location: staff.php');
                    exit();
                } else {
                    // If you do not want generic users, show an error or redirect to login
                    $error = 'Access denied. Only staff and admin accounts are allowed.';
                    // Optionally, you can log out the session or redirect to login page
                    // header('Location: login.php');
                    // exit();
                }
            }
        } else {
            error_log('Login failed for username: ' . $username . '. Password verification failed or user not found.');
            $_SESSION['login_attempts']++;
            if ($_SESSION['login_attempts'] >= 3) {
                $_SESSION['lockout_time'] = time() + 300;
                $error = 'Too many failed attempts. Please try again later.';
            } else {
                $error = 'Invalid username or password.';
            }
        }
    }
}

// Registration logic
$register_success = '';
$register_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    require_once 'db.php';

    // Ensure DB connection exists
    if (!isset($conn) || $conn === null) {
        error_log('Registration error: No database connection available.');
        $register_error = 'Unable to connect to the database. Please try again later.';
    } else {
        $reg_username = isset($_POST['reg_username']) ? trim($_POST['reg_username']) : '';
        $reg_email = isset($_POST['reg_email']) ? trim($_POST['reg_email']) : '';
        $reg_password = isset($_POST['reg_password']) ? (string)$_POST['reg_password'] : '';
        $reg_role = isset($_POST['reg_role']) ? trim($_POST['reg_role']) : '';

        // Whitelist roles to avoid invalid values
        $allowed_roles = ['staff', 'admin'];
        if (!in_array($reg_role, $allowed_roles, true)) {
            $reg_role = 'staff';
        }

        // Basic validation
        if ($reg_username === '' || $reg_email === '' || $reg_password === '') {
            $register_error = 'All fields are required.';
        } elseif (!filter_var($reg_email, FILTER_VALIDATE_EMAIL)) {
            $register_error = 'Please enter a valid email address.';
        } else {
            try {
                // Check if username or email already exists
                $stmt = $conn->prepare('SELECT COUNT(*) FROM users WHERE username = :username OR email = :email');
                $stmt->execute([':username' => $reg_username, ':email' => $reg_email]);
                if ((int)$stmt->fetchColumn() > 0) {
                    $register_error = 'Username or email already exists.';
                } else {
                    // Hash password
                    $hashed_password = password_hash($reg_password, PASSWORD_DEFAULT);

                    // Insert user (default status pending)
                    $stmt = $conn->prepare('INSERT INTO users (username, password, email, role, status) VALUES (:username, :password, :email, :role, :status)');
                    $success = $stmt->execute([
                        ':username' => $reg_username,
                        ':password' => $hashed_password,
                        ':email' => $reg_email,
                        ':role' => $reg_role,
                        ':status' => 'pending'
                    ]);

                    if ($success) {
                        $register_success = 'Account created successfully!';
                        error_log('Registration successful for username: ' . $reg_username . ' with role: ' . $reg_role);
                    } else {
                        $info = $stmt->errorInfo();
                        error_log('Registration insert failed. SQLSTATE: ' . ($info[0] ?? 'n/a') . ' DriverCode: ' . ($info[1] ?? 'n/a') . ' Message: ' . ($info[2] ?? 'n/a'));
                        $register_error = 'Error creating account. Please try again.';
                    }
                }
            } catch (Throwable $e) {
                error_log('Registration exception: ' . $e->getMessage());
                $register_error = 'A server error occurred while creating your account.';
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
    <title>Admin Login</title>
    <link rel="icon" type="image/png" href="images/iCenter.png">
    <link rel="shortcut icon" type="image/png" href="images/iCenter.png">
    <link rel="apple-touch-icon" href="images/iCenter.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- For password toggle -->
    <style>
        * {
            box-sizing: border-box;
        }
        
        p {
            color: white; /* golden yellow */
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
            background: rgba(20, 20, 20, 0.32); /* more transparent */
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
            position: relative; /* for pseudo-element */
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

        @media (max-width: 991px) {
            .logo-container {
                min-height: 220px;
                padding: 24px 10px 24px 10px;
            }
            .logo-container img {
                max-height: 140px;
                min-height: 80px;
            }
            .logo-container h1 {
                font-size: 2.1rem;
            }
            .logo-container p {
                font-size: 1.1rem;
            }
        }
        @media (max-width: 575px) {
            .logo-container {
                min-height: 120px;
                padding: 12px 2px 12px 2px;
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
        }
        @media (max-width: 480px) {
            .logo-container {
                min-height: 80px;
                padding: 8px 2px 8px 2px;
            }
            .logo-container img {
                max-height: 48px;
                min-height: 28px;
            }
            .logo-container h1 {
                font-size: 1rem;
            }
            .logo-container p {
                font-size: 0.75rem;
            }
        }
        @media (max-width: 375px) {
            .logo-container {
                min-height: 60px;
                padding: 4px 1px 4px 1px;
            }
            .logo-container img {
                max-height: 36px;
                min-height: 20px;
            }
            .logo-container h1 {
                font-size: 0.85rem;
            }
            .logo-container p {
                font-size: 0.65rem;
            }
        }

        .login-container {
            background: transparent;
            padding: 30px 15px;
            border-radius: 18px;
            text-align: center;
            width: 100%;
            max-width: 340px;
            min-width: 0;
            min-height: 480px;
            margin: 0;
            display: flex;
            flex-direction: column; 
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        #login-form-section, #register-form-section {
            width: 100%;
            min-height: 420px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .login-container h2 {
            font-size: 2rem;
            color: white;
            margin-bottom: 20px;
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
            background: linear-gradient(135deg,rgb(24, 162, 180), #07c926);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 1.2em;
            margin-top: 20px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }

        button:hover {
            background: white;
            box-shadow: 0 5px 15px rgba(10, 216, 42, 0.4);
        }

        button:active {
            transform: scale(0.98);
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .profile-section {
            text-align: center; /* Center the title */
            margin-bottom: 20px; /* Space below the title */
            animation: slideIn 1s; /* Slide in animation */
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .profile-section h1 {
            font-size: 5em; /* Increase font size */
            margin-bottom: 20px; /* Reduced space below the title */
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.7); /* Add text shadow for visibility */
        }

        .profile-section p {
            font-size: 1.5em; /* Increase font size for paragraph */
            text-shadow: 0 1px 5px rgba(0, 0, 0 , 0.5); /* Add text shadow for visibility */
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

        .login-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(255, 255, 255, 0.1),
                rgba(255, 255, 255, 0)
            );
            transform: rotate(30deg);
            z-index: -1;
        }

        .login-container h2 {
            font-size: 2em; /* Increase font size for the login title */
            color: #ffffff; /* Ensure color is white */
            margin-top: 0; /* Remove default margin */
        }

        .input-container {
            position: relative;
            margin: 20px auto; /* Add vertical spacing and center horizontally */
            width: 90%; /* Ensure the container doesn't stretch to the edges */
            max-width: 350px; /* Limit the maximum width for better balance */
        }

        .input-container input {
            width: 100%; /* Full width for better responsiveness */
            padding: 15px 40px 15px 45px; /* Add padding for the icon */
            margin: 0 auto; /* Center the input field horizontally */
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
            border-color:rgb(255, 255, 255);
            background-color: #444444;
        }

        .input-container label {
            position: absolute;
            top: 50%;
            left: 45px; /* Adjust for the icon */
            transform: translateY(-50%);
            font-size: 1rem;
            color: #aaaaaa;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .input-container input:focus + .input-label,
        .input-container input:not(:placeholder-shown) + .input-label {
            top: -10px;
            left: 45px; /* Adjust for the icon */
            font-size: 0.85rem;
            color:rgb(255, 255, 255);
            background-color: #121212;
            padding: 0 5px;
        }

        .input-icon {
            position: absolute;
            left: 15px; /* Position the icon inside the input field */
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2em;
            color: #aaaaaa;
            pointer-events: none;
            transition: color 0.3s ease;
        }

        .input-container input:focus ~ .input-icon {
            color:rgb(255, 255, 255); /* Change icon color when input is focused */
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #aaaaaa;
            transition: all 0.3s;
        }

        .toggle-password:hover {
            color: #0ad82a;
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



        .remember-me {
            display: flex;
            align-items: center; /* Center the checkbox and label vertically */
            margin-top: 10px; /* Space above the checkbox */
            color: #ffffff; /* White text color */
        }

        input[type="checkbox"] {
            width: 20px; /* Increase checkbox size */
            height: 20px; /* Increase checkbox size */
            margin-right: 10px; /* Space between checkbox and label */
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

        .password-strength {
            height: 4px;
            background: #333;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }

        .strength-meter {
            height: 100%;
            width: 0;
            background: #ff4c4c;
            transition: all 0.3s;
        }

        /* Sliding Switch Styles */
        .switch-container {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .switch-pill {
            position: relative;
            width: 320px;
            height: 48px;
            background: #000; /* Changed from #16213e to black */
            border-radius: 30px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            cursor: pointer;
            user-select: none;
        }
        .switch-slider {
            position: absolute;
            top: 4px;
            left: 4px;
            width: 152px;
            height: 40px;
            background: #fff; /* Changed from gradient to white */
            border-radius: 24px;
            transition: left 0.3s cubic-bezier(.4,2.08,.55,.44);
            z-index: 1;
        }
        .switch-option {
            flex: 1;
            text-align: center;
            z-index: 2;
            font-size: 1.1em;
            color: #fff;
            font-weight: 600;
            padding: 0 10px;
            transition: color 0.3s;
            line-height: 48px;
            border-radius: 30px;
            white-space: nowrap;
        }
        .switch-option.active {
            color: #000; /* Active tab text is black for contrast on white slider */
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
            
            .toggle-password {
                font-size: 1em;
            }
            
            button {
                padding: 10px;
                font-size: 1em;
            }
            
            .remember-me {
                font-size: 0.85rem;
            }
            
            input[type="checkbox"] {
                width: 16px;
                height: 16px;
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
            
            .toggle-password {
                font-size: 1em;
                right: 12px;
            }
            
            button {
                padding: 10px;
                font-size: 1em;
            }
            
            .remember-me {
                font-size: 0.85rem;
            }
            
            input[type="checkbox"] {
                width: 16px;
                height: 16px;
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
    <style>
        #reg_role {
            color: #fff !important;
            background: #222 !important;
            font-weight: 600;
        }
        #reg_role option {
            color: #000;
            background: #fff;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Left Side: Logo -->
        <div class="logo-container">
            <img src="images/iCenter.png" alt="Bislig iCenter Logo">
            <h1>Bislig iCenter</h1>
            <p>"No. 1 Supplier of iPhones in Mindanao"</p>
        </div>

        <!-- Right Side: Login Form -->
        <div class="login-container">
            <div class="switch-container">
                <div class="switch-pill">
                    <div class="switch-slider" id="switch-slider"></div>
                    <span id="switch-login" class="switch-option active">Log In</span>
                    <span id="switch-register" class="switch-option">Create Account</span>
                </div>
            </div>
            <?php if (!empty($register_success)) { echo "<div class='success-message' id='register-success-message'><p>$register_success</p></div>"; } ?>
            <div id="login-form-section">
                <h2>Login</h2>
                <?php 
                if (isset($error)) {
                    echo "<div class='error-message'><p>$error</p></div>";
                }
                ?>
                <form method="post" action="">
                    <div class="input-container">
                        <input type="text" name="username" id="username" placeholder=" " required>
                        <label for="username" class="input-label">Username</label>
                        <i class="fas fa-user input-icon"></i>
                    </div>
                    <div class="input-container">
                        <input type="password" name="password" id="password" placeholder=" " required>
                        <label for="password" class="input-label">Password</label>
                        <i class="fas fa-lock input-icon"></i>
                        <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility()"></i>
                    </div>
                    <div class="remember-me">
                        <input type="checkbox" name="remember_me" id="remember_me" <?php echo isset($_COOKIE['username']) ? 'checked' : ''; ?>>
                        <label for="remember_me">Remember Me</label>
                    </div>
                    <button type="submit" name="login">LOG IN</button>
                </form>
                <div style="margin-top: 10px;">
                    <a href="forgot_password.php" style="color: #fff;">Forgot Password?</a>
                </div>
            </div>
            <div id="register-form-section" style="display:none;">
                <h2 style="margin-top: 0;">Create Account</h2>
                <?php
                if (!empty($register_error)) {
                    echo "<div class='error-message'><p>$register_error</p></div>";
                }
                ?>
                <form method="post" action="">
                    <div class="input-container">
                        <input type="text" name="reg_username" placeholder=" " required>
                        <label class="input-label">Username</label>
                        <i class="fas fa-user input-icon"></i>
                    </div>
                    <div class="input-container">
                        <input type="email" name="reg_email" placeholder=" " required>
                        <label class="input-label">Email</label>
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                    <div class="input-container">
                        <input type="password" name="reg_password" placeholder=" " required>
                        <label class="input-label">Password</label>
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                    <div class="input-container">
                        <select name="reg_role" id="reg_role" style="width: 100%; padding: 10px; border-radius: 10px; margin-top: 5px;">
                            <option value="" disabled selected>Select Role</option>
                            <option value="staff">Staff</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <button type="submit" name="register">Register</button>
                </form>
            </div>
        </div>
    </div>

<script>
        // Function to toggle password visibility
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthMeter = document.querySelector('.strength-meter');
            let strength = 0;
            
            if (password.length > 0) strength += 1;
            if (password.length >= 8) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Update strength meter
            const width = strength * 20;
            strengthMeter.style.width = width + '%';
            
            // Update color
            if (strength <= 2) {
                strengthMeter.style.backgroundColor = '#ff4c4c';
            } else if (strength <= 4) {
                strengthMeter.style.backgroundColor = '#ffcc00';
            } else {
                strengthMeter.style.backgroundColor = '#0ad82a';
            }
        });

        // Form submission without loading state
        document.querySelector('form').addEventListener('submit', function(e) {
            // Remove loading state - direct submission
            const button = this.querySelector('button[type="submit"]');
            button.disabled = false;
        });

        // Input validation
        document.getElementById('username').addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.style.borderColor = '#ff4c4c';
            } else {
                this.style.borderColor = '#0ad82a';
            }
        });

        document.getElementById('password').addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.style.borderColor = '#ff4c4c';
            } else {
                this.style.borderColor = '#0ad82a';
            }
        });

        // Responsive touch improvements
        function isTouchDevice() {
            return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        }

        // Add touch-friendly improvements
        if (isTouchDevice()) {
            // Increase touch targets for mobile
            const inputs = document.querySelectorAll('input');
            const buttons = document.querySelectorAll('button');
            
            inputs.forEach(input => {
                input.style.minHeight = '44px'; // Minimum touch target size
            });
            
            buttons.forEach(button => {
                button.style.minHeight = '44px'; // Minimum touch target size
            });
            
            // Prevent zoom on input focus for iOS
            const viewport = document.querySelector('meta[name="viewport"]');
            if (viewport) {
                viewport.setAttribute('content', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no');
            }
        }

        // Handle orientation change
        window.addEventListener('orientationchange', function() {
            setTimeout(function() {
                window.scrollTo(0, 0);
            }, 100);
        });

        // Prevent form submission on Enter key for better mobile experience
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                // Allow form submission only if it's a proper form submit
                if (e.target.type === 'submit' || e.target.closest('form')) {
                    return;
                }
            }
        });

        // Sliding switch logic
        const switchLogin = document.getElementById('switch-login');
        const switchRegister = document.getElementById('switch-register');
        const switchSlider = document.getElementById('switch-slider');
        const loginFormSection = document.getElementById('login-form-section');
        const registerFormSection = document.getElementById('register-form-section');

        switchLogin.addEventListener('click', function() {
            switchSlider.style.left = '4px';
            switchLogin.classList.add('active');
            switchRegister.classList.remove('active');
            loginFormSection.style.display = '';
            registerFormSection.style.display = 'none';
        });
        switchRegister.addEventListener('click', function() {
            switchSlider.style.left = '164px';
            switchRegister.classList.add('active');
            switchLogin.classList.remove('active');
            loginFormSection.style.display = 'none';
            registerFormSection.style.display = '';
        });

        // After successful registration, switch to login tab and show message
        <?php if (!empty($register_success)) : ?>
            // Switch to login tab
            switchSlider.style.left = '4px';
            switchLogin.classList.add('active');
            switchRegister.classList.remove('active');
            loginFormSection.style.display = '';
            registerFormSection.style.display = 'none';
            // Scroll to the login form and show the success message
            document.getElementById('register-success-message').style.display = 'block';
        <?php endif; ?>
    </script>
</body>
</html>
