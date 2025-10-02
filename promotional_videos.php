<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Fetch video meta for all slots
$videoMeta = [];
$stmt = $conn->query("SELECT slot, title, description, filename, uploaded_at FROM promotional_videos WHERE is_archived = 0");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $videoMeta[$row['slot']] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Promotional Videos - Admin</title>
    <link rel="icon" type="image/png" href="images/iCenter.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Ensure Font Awesome 6.4.0 for consistent icons -->
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
      .video-placeholder {
        display: flex; align-items: center; justify-content: center;
        width: 100%; height: 100%;
        background-color: #f8fafc;
        color: #94a3b8;
        font-size: 3rem;
        border-radius: 0.75rem;
      }
      .spinner {
        border: 3px solid #e5e7eb; border-top: 3px solid #3b82f6; border-radius: 50%; width: 32px; height: 32px;
        animation: spin 1s linear infinite;
      }
      @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="min-h-screen">
    <header class="bg-gradient-to-r from-[#1a1a1a] to-[#2d2d2d] shadow-lg border-b border-white/10 sticky top-0 z-20 backdrop-blur-sm">
      <div class="flex justify-between items-center px-4 lg:px-8 py-4 lg:py-6">
        <div class="flex items-center space-x-3 lg:space-x-6">
          <img src="images/iCenter.png" alt="Logo" class="h-12 lg:h-20 w-auto border-2 border-white rounded-lg shadow-lg" />
          <span class="text-white text-lg font-semibold">Promotional Videos</span>
        </div>
      </div>
    </header>
    <div class="container mx-auto p-6 lg:p-8">
      <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Promotional Videos</h1>
        <div class="flex gap-4">
          <a href="admin.php" class="btn bg-white text-gray-700 font-semibold py-2 px-4 rounded-lg shadow-sm border border-gray-300 hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
          </a>
          <a href="archived_videos.php" class="btn bg-orange-500 text-white font-semibold py-2 px-4 rounded-lg shadow-sm hover:bg-orange-600">
              <i class="fas fa-archive mr-2"></i>View Archived
          </a>
        </div>
      </div>
      
      <div class="bg-white rounded-2xl shadow-lg p-8">
        <div class="flex items-center mb-2">
            <i class="ri-movie-2-line mr-3 text-2xl text-blue-600"></i>
            <h2 class="text-2xl font-bold text-gray-800">Manage Video Slots</h2>
        </div>
        <p class="text-gray-500 mb-8">Upload, manage, or archive the videos for your promotional slots.</p>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-8">
          <?php for ($slot = 1; $slot <= 4; $slot++): ?>
            <?php
              $title = isset($videoMeta[$slot]['title']) ? htmlspecialchars($videoMeta[$slot]['title']) : 'Video ' . $slot;
              $desc = isset($videoMeta[$slot]['description']) ? htmlspecialchars($videoMeta[$slot]['description']) : 'Upload a promotional video for your store or product.';
              $filename = isset($videoMeta[$slot]['filename']) ? $videoMeta[$slot]['filename'] : '';
              $videoUrl = '';
              if ($filename) {
                // Use a more reliable method to generate the video URL
                $videoUrl = 'promotional_videos/' . rawurlencode($filename);
              }
              $fileInfo = '';
              $uploadDate = isset($videoMeta[$slot]['uploaded_at']) ? $videoMeta[$slot]['uploaded_at'] : '';
              if ($filename && file_exists(__DIR__ . '/promotional_videos/' . $filename)) {
                $size = filesize(__DIR__ . '/promotional_videos/' . $filename);
                $sizeMB = number_format($size / 1048576, 2) . ' MB';
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $fileInfo = $sizeMB . ' • ' . strtoupper($ext);
              }
            ?>
            <div class="video-card p-4 text-center">
              <div class="flex items-center justify-center gap-2 mb-2">
                <span class="video-title text-lg font-bold text-gray-800"><?= $title ?></span>
                <button class="edit-title-btn text-gray-400 hover:text-blue-600 transition-colors duration-300" aria-label="Edit title and description"><i class="fas fa-pen-to-square"></i></button>
              </div>
              <p class="video-desc text-sm text-gray-500 mb-4 h-10"><?= $desc ?></p>
              
              <form class="edit-form hidden w-full flex flex-col items-center mb-4">
                <input type="text" class="edit-title-input border rounded-md px-2 py-1 mb-2 w-full text-center" value="<?= $title ?>" />
                <textarea class="edit-desc-input border rounded-md px-2 py-1 mb-2 w-full text-center h-20 resize-none"><?= $desc ?></textarea>
                <div class="flex gap-2">
                  <button type="button" class="save-edit-btn btn bg-blue-600 text-white font-semibold py-1 px-3 rounded-md text-sm">Save</button>
                  <button type="button" class="cancel-edit-btn btn bg-gray-200 text-gray-700 font-semibold py-1 px-3 rounded-md text-sm">Cancel</button>
                </div>
              </form>

              <div class="aspect-video w-full bg-gray-100 rounded-lg flex items-center justify-center shadow-inner border border-gray-200 relative overflow-hidden mb-4" style="min-height: 300px;">
                <?php if ($videoUrl): ?>
                  <video class="video-preview absolute inset-0 w-full h-full object-cover rounded-lg" controls controlsList="nodownload" style="min-height: 300px;" src="<?= $videoUrl ?>"></video>
                <?php else: ?>
                  <div class="video-placeholder" style="min-height: 300px;"><i class="ri-video-add-line"></i></div>
                <?php endif; ?>
                <input type="file" class="video-upload-input absolute inset-0 opacity-0 w-full h-full <?php echo !$videoUrl ? 'cursor-pointer' : 'pointer-events-none'; ?>" accept="video/*" />
                <div class="spinner hidden absolute"></div>
              </div>
              
              <div class="upload-progress-bar w-full h-1.5 bg-gray-200 rounded-full overflow-hidden mb-4 hidden"><div class="upload-progress-bar-inner bg-blue-500 h-full rounded-full"></div></div>

              <div class="video-info text-xs text-gray-400 mb-4 space-y-1">
                <?php if ($filename): ?>
                  <span><i class="ri-file-list-2-line mr-1"></i> <?= htmlspecialchars($filename) ?></span>
                  <span><i class="ri-computer-line mr-1"></i> <?= $fileInfo ?></span>
                  <span><i class="ri-time-line mr-1"></i> <?= htmlspecialchars($uploadDate) ?></span>
                <?php else: ?>
                  <span class="italic">No video uploaded</span>
                <?php endif; ?>
              </div>

              <div class="grid grid-cols-2 gap-2 w-full">
                <button type="button" class="btn bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg text-sm upload-btn"><i class="ri-upload-2-line mr-1"></i> Upload</button>
                <button type="button" class="btn bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 px-4 rounded-lg text-sm archive-btn" <?= !$filename ? 'disabled' : '' ?>><i class="ri-archive-line mr-1"></i> Archive</button>
              </div>
              <?php if ($filename): ?>
              <div class="mt-2">
                <button type="button" class="btn bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg text-sm delete-btn w-full"><i class="ri-delete-bin-line mr-1"></i> Delete</button>
              </div>
              <?php endif; ?>
            </div>
          <?php endfor; ?>
        </div>
    </div>
</div>
</div>
<!-- Add this modal HTML just before </body> -->
<div id="confirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
  <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md text-center">
    <div class="mb-4 text-lg font-semibold text-gray-800">Confirm Changes</div>
    <div class="mb-6 text-gray-600" id="confirmModalMessage">Are you sure you want to save changes to the title and description?</div>
    <div class="flex justify-center gap-4">
      <button id="confirmModalYes" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">Yes</button>
      <button id="confirmModalNo" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">Cancel</button>
    </div>
  </div>
</div>
<!-- Success Modal HTML -->
<div id="successModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
  <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md text-center">
    <div class="mb-4 text-lg font-semibold text-green-700 flex items-center justify-center gap-2">
      <i class="ri-checkbox-circle-line text-2xl text-green-600"></i>Success
    </div>
    <div class="mb-6 text-gray-700" id="successModalMessage">Title and description updated successfully!</div>
    <button id="successModalOk" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">OK</button>
  </div>
</div>
<script>
const existingFilenames = <?= json_encode(array_values(array_filter(array_column($videoMeta, 'filename')))) ?>;
// Simple confirm modal (replace with your own if needed)
function showConfirmModal({ message, onConfirm }) {
  const modal = document.getElementById('confirmModal');
  const msg = document.getElementById('confirmModalMessage');
  const yesBtn = document.getElementById('confirmModalYes');
  const noBtn = document.getElementById('confirmModalNo');
  msg.textContent = message;
  modal.classList.remove('hidden');
  function cleanup() {
    modal.classList.add('hidden');
    yesBtn.removeEventListener('click', onYes);
    noBtn.removeEventListener('click', onNo);
  }
  function onYes() {
    cleanup();
    if (onConfirm) onConfirm();
  }
  function onNo() {
    cleanup();
  }
  yesBtn.addEventListener('click', onYes);
  noBtn.addEventListener('click', onNo);
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
  }
  okBtn.addEventListener('click', onOk);
}


// Helper: Format bytes as MB
function formatBytes(bytes) {
  if (bytes === 0) return '0 MB';
  return (bytes / 1048576).toFixed(2) + ' MB';
}

// Helper: Get file extension
function getFileExtension(filename) {
  return filename.split('.').pop().toUpperCase();
}

document.querySelectorAll('.video-card').forEach(function(box, idx) {
  // Elements
  const editBtn = box.querySelector('.edit-title-btn');
  const titleSpan = box.querySelector('.video-title');
  const descSpan = box.querySelector('.video-desc');
  const form = box.querySelector('.edit-form');
  const titleInput = form.querySelector('.edit-title-input');
  const descInput = form.querySelector('.edit-desc-input');
  const saveBtn = form.querySelector('.save-edit-btn');
  const cancelBtn = form.querySelector('.cancel-edit-btn');
  const uploadInput = box.querySelector('.video-upload-input');
  const videoPreview = box.querySelector('.video-preview');
  const deleteBtn = box.querySelector('.bg-red-600');
  const uploadBtn = box.querySelector('.upload-btn');
  const icon = box.querySelector('.ri-video-line');
  const spinner = box.querySelector('.spinner');
  const progressBar = box.querySelector('.upload-progress-bar');
  const progressBarInner = box.querySelector('.upload-progress-bar-inner');

  // Edit title/desc
  if (editBtn) {
    editBtn.addEventListener('click', function() {
      form.classList.remove('hidden');
      titleSpan.style.display = 'none';
      descSpan.style.display = 'none';
      editBtn.style.display = 'none';
      titleInput.value = titleSpan.textContent;
      descInput.value = descSpan.textContent;
    });
  }

  if (saveBtn) {
    saveBtn.addEventListener('click', function() {
      showConfirmModal({
        message: 'Are you sure you want to save changes to the title and description?',
        onConfirm: function() {
          titleSpan.textContent = titleInput.value;
          descSpan.textContent = descInput.value;
          form.classList.add('hidden');
          titleSpan.style.display = '';
          descSpan.style.display = '';
          editBtn.style.display = '';

          fetch('save_promotional_video_meta.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              slot: idx + 1,
              title: titleInput.value,
              description: descInput.value
            })
          })
          .then(response => response.json())
          .then(data => {
            if (!data.success) {
              alert('Failed to save title/description: ' + data.message);
            } else {
              showSuccessModal('Title and description updated successfully!');
            }
          })
          .catch(() => {
            alert('An error occurred while saving the title/description.');
          });
        }
      });
    });
  }

  if (cancelBtn) {
    cancelBtn.addEventListener('click', function() {
      form.classList.add('hidden');
      titleSpan.style.display = '';
      descSpan.style.display = '';
      editBtn.style.display = '';
    });
  }

  // Upload video
  if (uploadBtn) {
    uploadBtn.addEventListener('click', function() {
      uploadInput.click();
    });
  }

  if (uploadInput) {
    uploadInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (!file) return;

      // Client-side validation
      const maxSize = 100 * 1024 * 1024; // 100MB
      if (file.size > maxSize) {
        alert('File size exceeds 100MB limit. Please choose a smaller file.');
        uploadInput.value = '';
        return;
      }

      // Validate file type first
      if (!file.type.startsWith('video/')) {
        alert('Please select a valid video file.');
        uploadInput.value = ''; // Reset input
        return;
      }

      // Check for duplicate filename
      const fileName = file.name;
      if (existingFilenames.includes(fileName)) {
        alert('A file with this name already exists. Please rename your file or choose a different one.');
        uploadInput.value = '';
        return;
      }

      // All upload logic goes here
      if (spinner) spinner.classList.remove('hidden');
        if (progressBar) {
          progressBar.style.display = 'block';
          progressBarInner.style.width = '0%';
        }
        uploadBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('video', file);
        formData.append('slot', idx + 1);
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'upload_promotional_video.php', true);
        
        xhr.upload.onprogress = function(progressEvent) {
          if (progressEvent.lengthComputable && progressBarInner) {
            const percent = (progressEvent.loaded / progressEvent.total) * 100;
            progressBarInner.style.width = percent + '%';
          }
        };
        
        xhr.onload = function() {
          uploadBtn.disabled = false;
          if (spinner) spinner.classList.add('hidden');
          if (progressBar) progressBar.style.display = 'none';
          
          if (xhr.status === 200) {
            let data;
            try {
              data = JSON.parse(xhr.responseText);
            } catch (err) {
              data = { success: false, message: 'Invalid response from server.' };
            }
            
            if (data.success) {
              showSuccessModal('Video uploaded successfully!');
              setTimeout(() => window.location.reload(), 1200);
            } else {
              alert('Upload failed: ' + (data.message || 'Unknown error'));
              uploadInput.value = ''; // Reset input on failure
            }
          } else {
            alert('An error occurred while uploading the video. Status: ' + xhr.status);
            uploadInput.value = ''; // Reset input on failure
          }
        };
        
        xhr.onerror = function() {
          uploadBtn.disabled = false;
          if (spinner) spinner.classList.add('hidden');
          if (progressBar) progressBar.style.display = 'none';
          alert('A network error occurred while uploading the video.');
          uploadInput.value = ''; // Reset input on failure
        };
        
        xhr.send(formData);
    });
  }

  // Archive video
  const archiveBtn = box.querySelector('.archive-btn');
  if (archiveBtn) {
    archiveBtn.addEventListener('click', function() {
      showConfirmModal({
        message: 'Are you sure you want to archive this video?',
        onConfirm: function() {
          if (spinner) spinner.classList.remove('hidden');
          fetch('archive_video.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ slot: idx + 1 })
          })
          .then(response => response.json())
          .then(data => {
            if (spinner) spinner.classList.add('hidden');
            if (data.success) {
              showSuccessModal('Video archived successfully!');
              setTimeout(() => window.location.reload(), 1200);
            } else {
              alert('Archive failed: ' + data.message);
            }
          })
          .catch(() => {
            if (spinner) spinner.classList.add('hidden');
            alert('An error occurred while archiving the video.');
          });
        }
      });
    });
  }

  // Delete video
  const deleteBtnElement = box.querySelector('.delete-btn');
  if (deleteBtnElement) {
    deleteBtnElement.addEventListener('click', function() {
      showConfirmModal({
        message: 'Are you sure you want to delete this video? This action cannot be undone.',
        onConfirm: function() {
          if (spinner) spinner.classList.remove('hidden');
          fetch('delete_promotional_video.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ slot: idx + 1 })
          })
          .then(response => response.json())
          .then(data => {
            if (spinner) spinner.classList.add('hidden');
            if (data.success) {
              showSuccessModal('Video deleted successfully!');
              setTimeout(() => window.location.reload(), 1200);
            } else {
              alert('Delete failed: ' + data.message);
            }
          })
          .catch(() => {
            if (spinner) spinner.classList.add('hidden');
            alert('An error occurred while deleting the video.');
          });
        }
      });
    });
  }

  // Always enable the edit button
  function updateEditBtnState() {
    if (editBtn) {
      editBtn.disabled = false;
      editBtn.classList.remove('opacity-50', 'pointer-events-none');
      editBtn.title = 'Edit Title & Description';
    }
  }
  updateEditBtnState();
});

</script>
</body>
</html>