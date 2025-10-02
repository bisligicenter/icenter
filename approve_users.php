<?php
session_start();
require_once 'db.php';

// Include PHPMailer (3rd-party) for sending emails
require_once 'phpmailer/PHPMailer-master/src/Exception.php';
require_once 'phpmailer/PHPMailer-master/src/PHPMailer.php';
require_once 'phpmailer/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Send approval/rejection email to a user
function sendUserDecisionEmail(string $toEmail, string $toName, string $decision): array {
    $result = ['success' => false, 'error' => null];
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'bisligicenter@gmail.com';
        $mail->Password = 'bdeypqafizvwarqz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('bisligicenter@gmail.com', 'Bislig iCenter');
        $mail->addReplyTo('bisligicenter@gmail.com', 'Bislig iCenter');

        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid recipient email');
        }
        $mail->addAddress($toEmail, $toName ?: $toEmail);

        $isApproved = strtolower($decision) === 'approved';
        $subject = $isApproved ? 'Your account has been approved' : 'Your account request status';
        $safeName = htmlspecialchars($toName ?: 'there', ENT_QUOTES, 'UTF-8');
        $ctaUrl = 'https://admin.bisligicenter.com/admin/login.php';
        if ($isApproved) {
            $body =
                '<h2>Hi ' . $safeName . ',</h2>'
                . '<p>Good news! 🎉 Your account has been <strong>approved</strong> by our admin team.</p>'
                . "<p>You can now log in and access your account at: <a href='" . htmlspecialchars($ctaUrl, ENT_QUOTES, 'UTF-8') . "'>Login Here</a></p>"
                . '<br>'
                . '<p>Thank you,<br>Bislig iCenter Team</p>';
        } else {
            $body =
                '<h2>Hi ' . $safeName . ',</h2>'
                . '<p>We regret to inform you that your account registration has been <strong>rejected</strong>.</p>'
                . '<p>If you believe this was a mistake or wish to reapply, please contact our support team.</p>'
                . '<br>'
                . '<p>Thank you,<br>Bislig iCenter Team</p>';
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $isApproved
            ? "Hi {$toName},\n\nGood news! Your account has been approved by our admin team.\nYou can now log in here: {$ctaUrl}\n\nThank you,\nBislig iCenter Team"
            : "Hi {$toName},\n\nWe regret to inform you that your account registration has been rejected.\nIf you believe this was a mistake or wish to reapply, please contact our support team.\n\nThank you,\nBislig iCenter Team";

        $mail->send();
        $result['success'] = true;
    } catch (Exception $e) {
        $result['error'] = 'Mailer error: ' . $e->getMessage();
    }
    return $result;
}

// Backward/alias function per request: call this to send user email
function sendUserEmail(string $toEmail, string $toName, string $decision): array {
    return sendUserDecisionEmail($toEmail, $toName, $decision);
}

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle approval action
$message = '';
$messageType = '';
if (isset($_POST['action'], $_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    // Fetch user details for email
    $userStmt = $conn->prepare('SELECT username, email FROM users WHERE id = :id');
    $userStmt->execute([':id' => $user_id]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($_POST['action'] === 'approve') {
            $stmt = $conn->prepare('UPDATE users SET status = :status, approved = 1 WHERE id = :id');
            $stmt->execute([':status' => 'approved', ':id' => $user_id]);
            $send = sendUserEmail($user['email'] ?? '', $user['username'] ?? '', 'approved');
            if ($send['success']) {
                $message = 'User approved and email sent.';
                $messageType = 'success';
            } else {
                $message = 'User approved but email failed to send.' . (!empty($send['error']) ? ' ' . $send['error'] : '');
                $messageType = 'error';
            }
        } elseif ($_POST['action'] === 'reject') {
            $stmt = $conn->prepare('UPDATE users SET status = :status WHERE id = :id');
            $stmt->execute([':status' => 'rejected', ':id' => $user_id]);
            $send = sendUserEmail($user['email'] ?? '', $user['username'] ?? '', 'rejected');
            if ($send['success']) {
                $message = 'User rejected and email sent.';
                $messageType = 'success';
            } else {
                $message = 'User rejected but email failed to send.' . (!empty($send['error']) ? ' ' . $send['error'] : '');
                $messageType = 'error';
            }
        }
    } else {
        $message = 'User not found.';
        $messageType = 'error';
    }
}

// Fetch all unapproved users (pending only)
$stmt = $conn->prepare('SELECT id, username, email, role FROM users WHERE approved = 0 AND (status IS NULL OR status = "pending")');
$stmt->execute();
$pendingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Approve Users - Admin Panel</title>
    <link rel="icon" type="image/png" href="images/iCenter.png">
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
      body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1a202c; min-height: 100vh; }
      .container { padding: 2rem; max-width: 900px; margin: 0 auto; }
      .content-card { background: #fff; border-radius: 20px; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; }
      h1 { font-size: 2.2rem; font-weight: 700; color: #1a202c; margin-bottom: 0.5rem; }
      .subtitle { color: #64748b; font-size: 1.1rem; margin-bottom: 2rem; }
      table { width: 100%; border-collapse: separate; border-spacing: 0; background: #fff; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; }
      th, td { padding: 1rem 1.5rem; border-bottom: 1px solid #e2e8f0; text-align: left; }
      th { background: #1a202c; color: #fff; font-weight: 600; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; }
      tr:hover { background: #f8fafc; transform: translateY(-1px); transition: all 0.3s ease; }
      tr:last-child td { border-bottom: none; }
      .btn { padding: 0.6rem 1.2rem; border-radius: 12px; font-weight: 600; font-size: 0.875rem; cursor: pointer; border: none; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
      .btn-approve { background: #0ad82a; color: #fff; border: 1px solid #0ad82a; }
      .btn-approve:hover { background: #07b21a; }
      .btn-reject { background: #d80a0a; color: #fff; border: 1px solid #d80a0a; margin-left: 8px; }
      .btn-reject:hover { background: #a30c0c; }
      .btn-back { background: #1a202c; color: #fff; border: 1px solid #1a202c; box-shadow: none; }
      .btn-back:hover { background: #2d3748; }
      .message { margin-bottom: 1.5rem; padding: 1rem 1.5rem; border-radius: 12px; font-weight: 600; border-left: 4px solid; }
      .message.success { background: #f0fdf4; color: #166534; border-left-color: #22c55e; }
      .message.error { background: #fef2f2; color: #dc2626; border-left-color: #ef4444; }
      .empty-state { text-align: center; padding: 4rem 2rem; color: #64748b; }
      .empty-state i { font-size: 4rem; margin-bottom: 1rem; opacity: 0.5; }
      @media (max-width: 1024px) {
        .container { padding: 1.5rem; }
        .content-card { padding: 1.5rem; }
        h1 { font-size: 2rem; }
      }
      @media (max-width: 768px) {
        .container { padding: 1rem; }
        .content-card { padding: 1rem; border-radius: 15px; }
        h1 { font-size: 1.5rem; }
        .subtitle { font-size: 1rem; }
        th, td { padding: 0.75rem 0.5rem; }
        .btn { padding: 0.5rem 1rem; font-size: 0.8rem; }
      }
      @media (max-width: 640px) {
        .container { padding: 0.75rem; }
        .content-card { padding: 0.75rem; border-radius: 12px; }
        h1 { font-size: 1.2rem; }
        .subtitle { font-size: 0.9rem; }
        th, td { padding: 0.5rem 0.25rem; }
        .btn { padding: 0.4rem 0.8rem; font-size: 0.75rem; gap: 0.25rem; }
      }
      @media (max-width: 480px) {
        .container { padding: 0.5rem; }
        .content-card { padding: 0.5rem; border-radius: 10px; }
        h1 { font-size: 1rem; }
        .subtitle { font-size: 0.8rem; }
        th, td { padding: 0.4rem 0.2rem; }
        .btn { padding: 0.35rem 0.7rem; font-size: 0.7rem; }
        .empty-state { padding: 2rem 1rem; }
        .empty-state i { font-size: 2.5rem; }
      }
      /* Responsive table for mobile */
      @media (max-width: 640px) {
        .overflow-x-auto { border-radius: 10px; overflow: hidden; }
        table { min-width: 100%; }
        table, thead, tbody, th, td, tr { display: block; }
        thead tr { position: absolute; top: -9999px; left: -9999px; }
        tr { border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 1rem; padding: 0.5rem; background: #f8fafc; }
        td { border: none; position: relative; padding: 0.5rem 0; padding-left: 50%; text-align: left; }
        td:before { content: attr(data-label); position: absolute; left: 0.5rem; width: 45%; font-weight: 600; color: #1a202c; }
      }
    </style>
</head>
<body>
  <header class="bg-gradient-to-r from-[#1a1a1a] to-[#2d2d2d] shadow-lg border-b border-white/10 sticky top-0 z-20 backdrop-blur-sm">
    <div class="flex justify-between items-center px-4 sm:px-6 lg:px-8 py-4 sm:py-6 space-x-2 sm:space-x-4">
      <div class="flex items-center space-x-2 sm:space-x-4 lg:space-x-6">
        <div class="ml-0 sm:ml-2 mr-2 sm:mr-6 lg:mr-10 text-xs sm:text-sm text-white flex items-center space-x-2 sm:space-x-4 lg:space-x-6">
          <img src="images/iCenter.png" alt="Logo" class="h-12 w-auto sm:h-16 lg:h-20 border-2 border-white rounded-lg shadow-lg mr-2 sm:mr-4" />
          <div class="flex flex-col space-y-1">
            <span class="font-semibold text-sm lg:text-lg" id="currentDate"></span>
            <div class="text-white/80 text-xs lg:text-sm">
              <i class="ri-time-line mr-1 lg:mr-2"></i>
              <span id="currentTime"></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
    <div class="container">
    <div class="content-card">
      <div class="mb-6 flex flex-wrap gap-4">
        <a href="admin.php" class="btn btn-back">
          <i class="fas fa-arrow-left"></i>
          Back to Dashboard
        </a>
      </div>
      <h1>Pending User Approvals</h1>
      <div class="subtitle">Approve or reject new user registrations below.</div>
      <?php if (!empty($message)) echo "<div class='message " . ($messageType === 'error' ? 'error' : 'success') . "'>$message</div>"; ?>
        <?php if (count($pendingUsers) === 0): ?>
        <div class="empty-state">
          <i class="fas fa-user-clock"></i>
          <h3 class="text-xl font-semibold mb-2">No Users Pending Approval</h3>
          <p>There are currently no users awaiting approval.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingUsers as $user): ?>
                    <tr>
                  <td data-label="Username"><?php echo htmlspecialchars($user['username']); ?></td>
                  <td data-label="Email"><?php echo htmlspecialchars($user['email']); ?></td>
                  <td data-label="Role"><?php echo htmlspecialchars($user['role']); ?></td>
                  <td data-label="Action">
                            <form method="post" style="display:inline;">
                      <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                      <button type="submit" name="action" value="approve" class="btn btn-approve"><i class="fas fa-check"></i>Approve</button>
                      <button type="submit" name="action" value="reject" class="btn btn-reject"><i class="fas fa-times"></i>Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
  </div>
  <!-- Email status modal -->
  <div id="emailStatusModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 max-w-md w-full p-6">
      <div class="flex items-center mb-4" id="emailStatusHeader">
        <div id="emailStatusIcon" class="mr-3"></div>
        <h3 id="emailStatusTitle" class="text-lg font-semibold text-gray-900"></h3>
      </div>
      <p id="emailStatusMessage" class="text-gray-700"></p>
      <div class="mt-6 text-right">
        <button id="emailStatusClose" class="btn btn-back">OK</button>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Current time display
      function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
          hour12: true, 
          hour: '2-digit', 
          minute: '2-digit', 
          second: '2-digit' 
        });
        const timeElement = document.getElementById('currentTime');
        if (timeElement) {
          timeElement.textContent = timeString;
        }
        // Date
        const dateString = now.toLocaleDateString('en-US', {
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
        const dateElement = document.getElementById('currentDate');
        if (dateElement) {
          dateElement.textContent = dateString;
        }
      }

      // Update time and date every second
      setInterval(updateTime, 1000);
      updateTime(); // Initial call

      // Email status modal logic
      const emailMessage = <?php echo json_encode($message ?? ''); ?>;
      const emailType = <?php echo json_encode($messageType ?? ''); ?>;
      if (emailMessage) {
        const modal = document.getElementById('emailStatusModal');
        const title = document.getElementById('emailStatusTitle');
        const message = document.getElementById('emailStatusMessage');
        const icon = document.getElementById('emailStatusIcon');
        const header = document.getElementById('emailStatusHeader');
        const closeBtn = document.getElementById('emailStatusClose');

        title.textContent = emailType === 'error' ? 'Email Failed' : 'Email Sent';
        message.textContent = emailMessage;
        if (emailType === 'error') {
          icon.innerHTML = '<i class="fas fa-times-circle text-red-600 text-2xl"></i>';
        } else {
          icon.innerHTML = '<i class="fas fa-check-circle text-green-600 text-2xl"></i>';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        function closeModal() {
          modal.classList.add('hidden');
          modal.classList.remove('flex');
        }
        closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function (e) {
          if (e.target === modal) closeModal();
        });
        document.addEventListener('keydown', function (e) {
          if (e.key === 'Escape') closeModal();
        });
      }
    });
  </script>
</body>
</html> 