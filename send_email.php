<?php
session_start();
require_once 'db.php';

// Include PHPMailer
require 'phpmailer/PHPMailer-master/src/Exception.php';
require 'phpmailer/PHPMailer-master/src/PHPMailer.php';
require 'phpmailer/PHPMailer-master/src/SMTP.php';

// Use statements must be at the top level
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

$subject = trim($input['subject'] ?? '');
$message = trim($input['message'] ?? '');
$sendToAll = $input['sendToAll'] ?? false;
$selectedCustomers = $input['selectedCustomers'] ?? [];

if (empty($subject) || empty($message)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Subject and message are required']);
    exit();
}

try {

    // Get customer emails
    $emails = [];
    if ($sendToAll) {
        $stmt = $conn->query("SELECT DISTINCT email FROM reservations WHERE email IS NOT NULL AND email != '' AND name IS NOT NULL AND name != ''");
        $emails = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $emails = $selectedCustomers;
    }

    if (empty($emails)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No valid email addresses found']);
        exit();
    }

    // Create PHPMailer instance
    $mail = new PHPMailer(true);

    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // You can change this to your SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'bisligicenter@gmail.com'; // Replace with your email
    $mail->Password = 'bdeypqafizvwarqz'; // Replace with your app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('bisligicenter@gmail.com', 'Bislig iCenter'); // Replace with your email
    $mail->addReplyTo('bisligicenter@gmail.com', 'Bislig iCenter'); // Replace with your email

    // Add recipients
    foreach ($emails as $email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mail->addAddress($email);
        }
    }

    // Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = nl2br($message);
    $mail->AltBody = strip_tags($message);

    // Send email
    $mail->send();

    // Log the email sending
    $logMessage = "Email sent to " . count($emails) . " customers. Subject: " . $subject;
    error_log($logMessage);

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Email sent successfully to ' . count($emails) . ' customers'
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Email could not be sent. Mailer Error: ' . $mail->ErrorInfo
    ]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?> 