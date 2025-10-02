<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Fetch archived video meta
$archivedVideos = [];
$stmt = $conn->query("SELECT id, slot, title, description, filename, uploaded_at FROM promotional_videos WHERE is_archived = 1 ORDER BY slot");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $archivedVideos[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Archived Videos - Admin</title>
    <link rel="icon" type="image/png" href="images/iCenter.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <style>
      body {
        font-family: 'Inter', sans-serif;
        background-color: #f1f5f9;
      }
      .btn {
        transition: all 0.3s ease;
      }
      .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      }
      .video-card {
        background-color: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
      }
      .video-card:hover {
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        transform: translateY(-4px);
      }
    </style>
</head>
<body class="min-h-screen">
    <header class="bg-gradient-to-r from-[#1a1a1a] to-[#2d2d2d] shadow-lg border-b border-white/10 sticky top-0 z-20 backdrop-blur-sm">
      <div class="flex justify-between items-center px-4 lg:px-8 py-4 lg:py-6">
        <div class="flex items-center space-x-3 lg:space-x-6">
          <img src="images/iCenter.png" alt="Logo" class="h-12 lg:h-20 w-auto border-2 border-white rounded-lg shadow-lg" />
          <span class="text-white text-lg font-semibold">Archived Videos</span>
        </div>
      </div>
    </header>
    <div class="container mx-auto p-6 lg:p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Archived Videos</h1>
            <a href="promotional_videos.php" class="btn bg-white text-gray-700 font-semibold py-2 px-4 rounded-lg shadow-sm border border-gray-300 hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>Back to Active Videos
            </a>
        </div>

      <div class="bg-white rounded-2xl shadow-lg p-8">
        <div class="flex items-center mb-2">
            <i class="ri-archive-line mr-3 text-2xl text-orange-600"></i>
            <h2 class="text-2xl font-bold text-gray-800">Archived Promotional Videos</h2>
        </div>
        <p class="text-gray-500 mb-8">These videos are archived and not visible on the main page. You can unarchive them or delete them permanently.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
          <?php if (empty($archivedVideos)): ?>
            <p class="text-gray-500 col-span-full text-center">There are no archived videos.</p>
          <?php else: ?>
            <?php foreach ($archivedVideos as $video): ?>
              <?php
                $videoUrl = 'promotional_videos/' . htmlspecialchars($video['filename']);
              ?>
              <div class="video-card p-4 text-center">
                <span class="text-lg font-bold text-gray-800"><?= htmlspecialchars($video['title']) ?></span>
                <p class="text-sm text-gray-500 mb-4">Original Slot: <?= $video['slot'] ?></p>
                
                <div class="aspect-video w-full bg-gray-100 rounded-lg flex items-center justify-center shadow-inner border border-gray-200 relative overflow-hidden mb-4">
                  <video class="w-full h-full object-cover rounded-lg" controls controlsList="nodownload" src="<?= $videoUrl ?>"></video>
                </div>

                <div class="grid grid-cols-2 gap-2 w-full">
                  <button type="button" class="btn bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-2 px-4 rounded-lg text-sm unarchive-btn" data-slot="<?= $video['slot'] ?>"><i class="ri-inbox-unarchive-line mr-1"></i> Unarchive</button>
                  <button type="button" class="btn bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg text-sm delete-permanently-btn" data-id="<?= $video['id'] ?>" data-filename="<?= $video['filename'] ?>"><i class="ri-delete-bin-line mr-1"></i> Delete</button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

<!-- Modal for confirmations -->
<div id="confirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
  <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md text-center">
    <div class="mb-4 text-lg font-semibold text-gray-800" id="confirmModalTitle">Confirm Action</div>
    <div class="mb-6 text-gray-600" id="confirmModalMessage">Are you sure?</div>
    <div class="flex justify-center gap-4">
      <button id="confirmModalYes" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">Yes</button>
      <button id="confirmModalNo" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
    </div>
  </div>
</div>

<!-- Success Modal HTML -->
<div id="successModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
  <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md text-center">
    <div class="mb-4 text-lg font-semibold text-green-700 flex items-center justify-center gap-2">
      <i class="ri-checkbox-circle-line text-2xl text-green-600"></i>Success
    </div>
    <div class="mb-6 text-gray-700" id="successModalMessage"></div>
    <button id="successModalOk" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">OK</button>
  </div>
</div>

<script>
function showConfirmModal({ title, message, onConfirm }) {
  const modal = document.getElementById('confirmModal');
  document.getElementById('confirmModalTitle').textContent = title;
  document.getElementById('confirmModalMessage').textContent = message;
  modal.classList.remove('hidden');
  
  const yesBtn = document.getElementById('confirmModalYes');
  const noBtn = document.getElementById('confirmModalNo');

  function cleanup() {
    modal.classList.add('hidden');
    yesBtn.replaceWith(yesBtn.cloneNode(true)); // Remove event listeners
    noBtn.replaceWith(noBtn.cloneNode(true));
  }

  document.getElementById('confirmModalYes').addEventListener('click', () => {
    cleanup();
    if (onConfirm) onConfirm();
  });

  document.getElementById('confirmModalNo').addEventListener('click', cleanup);
}

function showSuccessModal(message) {
  const modal = document.getElementById('successModal');
  const msg = document.getElementById('successModalMessage');
  const okBtn = document.getElementById('successModalOk');
  
  msg.textContent = message;
  modal.classList.remove('hidden');

  function cleanup() {
    modal.classList.add('hidden');
    okBtn.removeEventListener('click', onOk);
  }

  function onOk() {
    cleanup();
    window.location.reload();
  }

  okBtn.addEventListener('click', onOk);
}

document.querySelectorAll('.unarchive-btn').forEach(button => {
  button.addEventListener('click', function() {
    const slot = this.dataset.slot;
    showConfirmModal({
      title: 'Unarchive Video',
      message: `Are you sure you want to unarchive the video from slot ${slot}? This will make it active again.`,
      onConfirm: () => {
        fetch('unarchive_video.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ slot: slot })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            showSuccessModal('Video unarchived successfully!');
          } else {
            alert('Error: ' + data.message);
          }
        });
      }
    });
  });
});

document.querySelectorAll('.delete-permanently-btn').forEach(button => {
  button.addEventListener('click', function() {
    const id = this.dataset.id;
    const filename = this.dataset.filename;
    showConfirmModal({
      title: 'Delete Permanently',
      message: 'Are you sure you want to permanently delete this video? This action cannot be undone.',
      onConfirm: () => {
        fetch('delete_video_permanently.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: id, filename: filename })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            showSuccessModal('Video deleted permanently!');
          } else {
            alert('Error: ' + data.message);
          }
        });
      }
    });
  });
});
</script>

</body>
</html>