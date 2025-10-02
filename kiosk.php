<?php
require_once 'db.php';
require_once 'functions.php';
$conn = getConnection();

// Fetch promotional videos
$promotionalVideos = [];
try {
    $stmt = $conn->query("SELECT slot, title, description, filename FROM promotional_videos WHERE is_archived = 0 ORDER BY slot");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $promotionalVideos[$row['slot']] = $row;
    }
} catch (Exception $e) {
    // Handle error silently for kiosk display
    error_log("Error fetching promotional videos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>BISLIG iCENTER</title>
  <link rel="icon" type="image/png" href="images/iCenter.png">
  <link rel="shortcut icon" type="image/png" href="images/iCenter.png">
  <link rel="apple-touch-icon" href="images/iCenter.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&family=Roboto&family=Roboto+Slab&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="kiosk.css">
  <style>
    /* Promotional Videos Slider Styles */
    .promo-videos-container {
      background: rgba(255, 255, 255, 0.05);
      padding: 40px 0;
      margin: 20px 0;
      position: relative;
      overflow: hidden;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    /* remove overlay to avoid overlapping shadow */
    .promo-videos-container::before { display: none !important; }

    /* --- Enhanced Details Modal Styles --- */
    .details-modal-content {
      max-width: 56rem; /* 1024px, wider for landscape */
      width: 95%;
    }
    .modal-thumbnails img {
      width: 64px;
      height: 64px;
      object-fit: cover;
      border-radius: 8px;
      cursor: pointer;
      border: 2px solid transparent;
      transition: all 0.3s ease;
    }
    

    /* --- Consolidated Modal Styles (from kioskmodals.php) --- */
    .modal .close {
      background: #000 !important;
      color: #fff !important;
    }
    
    .modal .close:hover {
      color: #fff;
      background: #000;
      transform: scale(1.08) rotate(90deg);
      box-shadow: 0 4px 16px rgba(0,0,0,0.18);
    }
    
    #contactUsModal .care-modal-close:hover,
    #howToOrderModal .care-modal-close:hover,
    #returnsRefundsModal .care-modal-close:hover,
    #warrantyModal .care-modal-close:hover {
      background: #000 !important;
      color: #fff !important;
      transform: scale(1.08) rotate(90deg);
      box-shadow: 0 4px 16px rgba(0,0,0,0.18);
    }
    
    .care-modal.show,
    .care-modal.customer-care-active {
      display: block !important;
      opacity: 1 !important;
      visibility: visible !important;
      z-index: 100000 !important;
    }
    
    .care-modal.show .modal-content,
    .care-modal.customer-care-active .modal-content {
      display: block !important;
      opacity: 1 !important;
      visibility: visible !important;
    }
    
    #contactUsModal.show,
    #contactUsModal.customer-care-active {
      display: block !important;
      opacity: 1 !important;
      visibility: visible !important;
      z-index: 100000 !important;
      pointer-events: auto !important;
    }

    .care-modal {
      background: rgba(0,0,0,0.55);
      backdrop-filter: blur(2px);
      transition: opacity .25s ease, visibility .25s ease;
    }

    .care-modal .modal-content.care-modal-content {
      border-radius: 18px;
      border: 1px solid rgba(0,0,0,0.08);
      box-shadow: 0 20px 60px rgba(0,0,0,0.25);
      background: linear-gradient(180deg, #ffffff 0%, #fafafa 100%);
      padding: 20px 22px;
      max-width: 560px;
      width: 92vw;
      animation: careModalIn .22s ease-out;
    }

    @keyframes careModalIn {
      from { transform: translateY(10px) scale(.98); opacity: .85; }
      to   { transform: translateY(0) scale(1); opacity: 1; }
    }

    .care-modal-header {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 6px 6px 14px 6px;
      border-bottom: 1px solid #efefef;
    }

    .care-modal-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      border-radius: 12px;
      background: #000;
      color: #fff;
      box-shadow: 0 8px 18px rgba(0,0,0,0.18);
    }

    .care-modal-title {
      font-size: 1.15rem;
      font-weight: 800;
      letter-spacing: .2px;
      color: #111;
      margin: 0;
    }

    .care-modal-close {
      margin-left: auto;
      border: none;
      width: 36px;
      height: 36px;
      border-radius: 10px;
      background: #f1f2f4;
      color: #333;
      cursor: pointer;
      transition: all .18s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .care-modal-close:hover {
      background: #000;
      color: #fff;
      transform: scale(1.06);
      box-shadow: 0 10px 24px rgba(0,0,0,0.2);
    }

    .care-modal-body {
      padding: 16px 6px 4px 6px;
      color: #333;
      line-height: 1.6;
    }

    .care-modal-intro {
      text-align: center;
      color: #555;
      margin-bottom: 24px;
      font-size: 1.05rem;
    }
    
    .promo-videos-content {
      position: relative;
      z-index: 2;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    .promo-videos-title {
      text-align: center;
      color: #333;
      margin-bottom: 30px;
    }
    
    .promo-videos-title h2 {
      font-size: 2.5rem;
      font-weight: bold;
      margin-bottom: 10px;
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
    }
    
    .promo-videos-title p {
      font-size: 1.1rem;
      opacity: 0.8;
    }
    
    .promo-videos-slider {
      position: relative;
      max-width: 1400px;
      margin: 0 auto;
      overflow: hidden;
    }
    
    .promo-videos-track {
      display: flex;
      transition: transform 0.5s ease-in-out;
      gap: 20px;
      padding: 0 10px;
    }
    
    .promo-video-slide {
      min-width: calc(33.333% - 14px);
      flex-shrink: 0;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 15px;
      padding: 20px;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(0, 0, 0, 0.1);
      box-shadow: none !important;
      transition: transform 0.3s ease;
    }
    
    .promo-video-slide:hover {
      transform: translateY(-5px);
      box-shadow: none !important;
    }
    
    .promo-video-content {
      text-align: center;
      color: #333;
    }
    
    .promo-video-title {
      font-size: 1.3rem;
      font-weight: bold;
      margin-bottom: 10px;
      text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
      line-height: 1.3;
    }
    
    .promo-video-description {
      font-size: 0.9rem;
      margin-bottom: 15px;
      opacity: 0.8;
      line-height: 1.4;
      height: 2.8em;
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
    }
    
    .promo-video-player {
      width: 100%;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: none !important;
      position: relative;
      background: #000;
    }
    
    .promo-video-player video {
      width: 100%;
      height: 180px;
      display: block;
      object-fit: cover;
      background: #000;
    }
    
    .promo-video-player::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(45deg, #f0f0f0 25%, transparent 25%), 
                  linear-gradient(-45deg, #f0f0f0 25%, transparent 25%), 
                  linear-gradient(45deg, transparent 75%, #f0f0f0 75%), 
                  linear-gradient(-45deg, transparent 75%, #f0f0f0 75%);
      background-size: 20px 20px;
      background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
      opacity: 0.3;
      z-index: 1;
    }
    
    .promo-video-player video {
      position: relative;
      z-index: 2;
      background: #000;
      border-radius: 10px;
    }
    
    /* Video loading states */
    .promo-video-player video:not([src]) {
      background: linear-gradient(45deg, #f0f0f0 25%, transparent 25%), 
                  linear-gradient(-45deg, #f0f0f0 25%, transparent 25%), 
                  linear-gradient(45deg, transparent 75%, #f0f0f0 75%), 
                  linear-gradient(-45deg, transparent 75%, #f0f0f0 75%);
      background-size: 20px 20px;
      background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
    }
    
    /* Ensure video controls are visible */
    .promo-video-player video::-webkit-media-controls {
      background: rgba(0, 0, 0, 0.7);
    }
    
    .promo-video-player video::-webkit-media-controls-panel {
      background: rgba(0, 0, 0, 0.7);
    }
    
    .promo-slider-controls {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-top: 30px;
      gap: 20px;
    }
    
    .promo-slider-btn {
      background: rgba(0, 0, 0, 0.1);
      border: 2px solid rgba(0, 0, 0, 0.2);
      color: #333;
      padding: 12px 20px;
      border-radius: 25px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 1rem;
      backdrop-filter: blur(10px);
    }
    
    .promo-slider-btn:hover {
      background: rgba(0, 0, 0, 0.2);
      transform: translateY(-2px);
      box-shadow: none;
    }
    
    .promo-slider-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
    }
    
    .promo-slider-dots {
      display: flex;
      gap: 10px;
      margin: 0 20px;
    }
    
    .promo-slider-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: rgba(0, 0, 0, 0.3);
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .promo-slider-dot.active {
      background: #333;
      transform: scale(1.2);
    }
    
    .promo-video-placeholder {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 300px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 10px;
      color: white;
      font-size: 1.2rem;
      opacity: 0.7;
    }
    
    @media (max-width: 1024px) {
      .promo-video-slide {
        min-width: calc(50% - 10px);
      }
      
      .promo-video-title {
        font-size: 1.2rem;
      }
      
      .promo-video-description {
        font-size: 0.85rem;
      }
      
      .promo-video-player video {
        height: 160px;
      }
    }
    
    @media (max-width: 768px) {
      .promo-videos-title h2 {
        font-size: 2rem;
      }
      
      .promo-video-slide {
        min-width: calc(100% - 20px);
        padding: 15px;
      }
      
      .promo-video-title {
        font-size: 1.1rem;
      }
      
      .promo-video-description {
        font-size: 0.8rem;
        height: auto;
        -webkit-line-clamp: 3;
      }
      
      .promo-video-player video {
        height: 200px;
      }
      
      .promo-slider-controls {
        flex-direction: column;
        gap: 15px;
      }
    }
    
    @media (max-width: 480px) {
      .promo-videos-title h2 {
        font-size: 1.8rem;
      }
      
      .promo-video-title {
        font-size: 1rem;
      }
      
      .promo-video-description {
        font-size: 0.75rem;
      }
      
      .promo-video-player video {
        height: 150px;
      }
      
      .promo-video-slide {
        padding: 12px;
      }
    }
    
    /* Ensure Crisp chat widget is visible */
    #crisp-chatbox {
      z-index: 9999 !important;
    }
    
    .crisp-client {
      z-index: 9999 !important;
    }

    /* Customer Care Dropdown Styles */
    .customer-care-dropdown {
      position: absolute;
      top: 56px;
      right: 40px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.13);
      padding: 12px 0;
      min-width: 220px;
      z-index: 99999;
      display: none;
      flex-direction: column;
      gap: 0;
      border: 1px solid #eee;
    }
    .customer-care-dropdown.show {
      display: flex;
      animation: careDropdownIn .18s ease;
    }
    @keyframes careDropdownIn {
      from { opacity: 0; transform: translateY(-8px);}
      to   { opacity: 1; transform: translateY(0);}
    }
    .customer-care-dropdown button {
      background: none;
      border: none;
      width: 100%;
      text-align: left;
      padding: 12px 24px;
      font-size: 1rem;
      color: #222;
      cursor: pointer;
      transition: background .18s;
    }
    .customer-care-dropdown button:hover {
      background: #f5f7fa;
      color: #007bff;
    }

    /* --- Search Suggestion Styles --- */
    .search-container {
      position: relative;
      max-width: 600px; /* Increased from 400px */
      margin: 0 auto;
      z-index: 10000;
    }
    .search-container input {
      width: 100%;
      min-width: 350px; /* Ensure it's wide even on small screens */
      padding: 12px 48px 12px 16px;
      border: 1px solid #ddd;
      border-radius: 24px;
      font-size: 1.1rem; /* Slightly larger font */
      color: #333;
      background: #fff;
      transition: border-color 0.3s ease;
    }
    .search-container input:focus {
      border-color: #007bff;
      outline: none;
    }
    .search-container button {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      background: transparent;
      border: none;
      cursor: pointer;
      padding: 0;
      color: #007bff;
      font-size: 1.2rem;
    }
    .search-container button:hover {
      color: #0056b3;
    }
    .search-suggestions {
      display: none;
      position: absolute;
      top: 44px;
      left: 0;
      right: 0;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.08);
      z-index: 10001;
      max-height: 320px;
      overflow-y: auto;
    }
    .suggestion-item {
      padding: 12px 18px;
      cursor: pointer;
      border-bottom: 1px solid #f0f0f0;
    }
    .suggestion-item:hover {
      background: #f5f7fa;
    }
    .suggestion-item strong {
      display: block;
      color: #333;
    }
    .suggestion-item span {
      color: #888;
      font-size: 0.95em;
    }
    .suggestion-item span.price {
      float: right;
      color: #007dd1;
      font-weight: 600;
    }
  </style>
</head>

<body>
  <?php include 'kioskheader.php'; ?>

  <!-- Customer Care Dropdown (inject after header) -->
  <div id="customerCareDropdown" class="customer-care-dropdown" role="menu" aria-label="Customer Care Options">
    <button data-modal="contactUsModal">Contact Us</button>
    <button data-modal="howToOrderModal">How to Order</button>
    <button data-modal="returnsRefundsModal">Returns &amp; Refunds</button>
    <button data-modal="warrantyModal">Warranty</button>
  </div>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Dropdown logic
    const careLink = document.getElementById('customerCareLink');
    const dropdown = document.getElementById('customerCareDropdown');
    let dropdownTimeout;

    function showDropdown() {
      clearTimeout(dropdownTimeout);
      dropdown.classList.add('show');
    }
    function hideDropdown() {
      dropdownTimeout = setTimeout(() => {
        dropdown.classList.remove('show');
      }, 180);
    }

    // Position dropdown below the button
    function positionDropdown() {
      if (!careLink || !dropdown) return;
      const rect = careLink.getBoundingClientRect();
      dropdown.style.top = (rect.bottom + window.scrollY) + "px";
      dropdown.style.right = (window.innerWidth - rect.right - 8) + "px";
    }

    careLink.addEventListener('mouseenter', function() {
      positionDropdown();
      showDropdown();
    });
    careLink.addEventListener('mouseleave', hideDropdown);
    dropdown.addEventListener('mouseenter', showDropdown);
    dropdown.addEventListener('mouseleave', hideDropdown);

    // Open modals from dropdown
    dropdown.querySelectorAll('button[data-modal]').forEach(btn => {
      btn.addEventListener('click', function() {
        hideDropdown();
        const modalId = btn.getAttribute('data-modal');
        openCareModal(modalId);
      });
    });

    // Modal logic
    const careModals = [
      'contactUsModal',
      'howToOrderModal',
      'returnsRefundsModal',
      'warrantyModal'
    ];
    function closeAllCareModals() {
      careModals.forEach(id => {
        const modal = document.getElementById(id);
        if (modal) {
          modal.classList.remove('show', 'customer-care-active');
          modal.setAttribute('aria-hidden', 'true');
        }
      });
    }
    function openCareModal(id) {
      closeAllCareModals();
      const modal = document.getElementById(id);
      if (modal) {
        modal.classList.add('show', 'customer-care-active');
        modal.setAttribute('aria-hidden', 'false');
        modal.focus();
      }
    }
    // Add close button logic for all care modals
    careModals.forEach(id => {
      const modal = document.getElementById(id);
      if (modal) {
        const closeBtn = modal.querySelector('.care-modal-close, .close');
        if (closeBtn) {
          closeBtn.addEventListener('click', function() {
            modal.classList.remove('show', 'customer-care-active');
            modal.setAttribute('aria-hidden', 'true');
          });
        }
        // Close modal on outside click
        modal.addEventListener('click', function(e) {
          if (e.target === modal) {
            modal.classList.remove('show', 'customer-care-active');
            modal.setAttribute('aria-hidden', 'true');
          }
        });
        // Trap focus inside modal for accessibility
        modal.addEventListener('keydown', function(e) {
          if (e.key === 'Escape') {
            modal.classList.remove('show', 'customer-care-active');
            modal.setAttribute('aria-hidden', 'true');
          }
        });
      }
    });
  });
  </script>

  <!-- Promotional Videos Slider -->
  <?php if (!empty($promotionalVideos)): ?>
<div class="promo-videos-container" style="padding: 40px 0; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); margin: 20px 0;">
  <div class="promo-videos-content" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
    <div class="promo-single-video-player" style="position:relative; border-radius: 20px; overflow: hidden; box-shadow: none; background: #000;">
      <video id="promoAdVideo" controls controlsList="nodownload" autoplay muted playsinline preload="auto"
        style="width:100%; height: 40vw; max-height:600px; min-height:200px; object-fit: cover; display: block; background:#000;">
        <source src="promotional_videos/<?= htmlspecialchars(reset($promotionalVideos)['filename']) ?>" type="video/mp4">
        Your browser does not support the video tag.
      </video>
      <!-- Progress indicator -->
      <div id="videoProgress" style="position: absolute; bottom: 0; left: 0; right: 0; height: 4px; background: rgba(255,255,255,0.3); z-index: 2;">
        <div id="progressBar" style="height: 100%; background: linear-gradient(90deg, #ff6b6b, #4ecdc4); width: 0%; transition: width 0.1s ease;"></div>
      </div>
      <!-- Video counter -->
      <div id="videoCounter" style="position: absolute; top: 15px; right: 15px; background: rgba(0,0,0,0.7); color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px; z-index: 2;">
        <span id="currentVideoNum">1</span> / <span id="totalVideos"><?= count($promotionalVideos) ?></span>
      </div>
      <!-- Title & Description -->
      <div id="promoAdTitle" style="text-align:center;font-weight:bold;font-size:1.2em;margin-top:0.5em;color:#333;">
        <?= htmlspecialchars(reset($promotionalVideos)['title']) ?>
      </div>
      <div id="promoAdDesc" style="text-align:center;font-size:1em;color:#666;max-width:90%;margin:0.5em auto 0;">
        <?= htmlspecialchars(reset($promotionalVideos)['description']) ?>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const promoVideos = <?php echo json_encode(array_values($promotionalVideos)); ?>;
  let currentIdx = 0;
  const video = document.getElementById('promoAdVideo');
  const progressBar = document.getElementById('progressBar');
  const currentVideoNum = document.getElementById('currentVideoNum');
  const title = document.getElementById('promoAdTitle');
  const desc = document.getElementById('promoAdDesc');

  function playVideo(idx) {
    if (!promoVideos[idx]) return;
    video.style.opacity = '0.7';
    setTimeout(() => {
      video.src = 'promotional_videos/' + encodeURIComponent(promoVideos[idx].filename);
      title.textContent = promoVideos[idx].title;
      desc.textContent = promoVideos[idx].description;
      currentVideoNum.textContent = idx + 1;
      video.load();
      video.play();
      video.style.opacity = '1';
    }, 200);
  }

  video.addEventListener('timeupdate', function() {
    if (video.duration) {
      const progress = (video.currentTime / video.duration) * 100;
      progressBar.style.width = progress + '%';
    }
  });

  video.addEventListener('ended', function() {
    currentIdx = (currentIdx + 1) % promoVideos.length;
    playVideo(currentIdx);
  });

  video.addEventListener('error', function() {
    currentIdx = (currentIdx + 1) % promoVideos.length;
    playVideo(currentIdx);
  });

  video.addEventListener('click', function(e) {
    if (e.target === video) {
      currentIdx = (currentIdx + 1) % promoVideos.length;
      playVideo(currentIdx);
    }
  });

  video.addEventListener('loadstart', function() {
    progressBar.style.width = '0%';
  });

  // Keyboard navigation (left/right/space)
  document.addEventListener('keydown', function(e) {
    const ae = document.activeElement;
    if (ae && ((ae.tagName === 'INPUT') || (ae.tagName === 'TEXTAREA') || ae.isContentEditable)) return;
    if (e.key === 'ArrowRight' || e.key === ' ') {
      e.preventDefault();
      currentIdx = (currentIdx + 1) % promoVideos.length;
      playVideo(currentIdx);
    } else if (e.key === 'ArrowLeft') {
      e.preventDefault();
      currentIdx = (currentIdx - 1 + promoVideos.length) % promoVideos.length;
      playVideo(currentIdx);
    }
  });

  // Responsive video height
  function updateVideoHeight() {
    const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
    video.style.height = vw < 480 ? '200px' : vw < 768 ? '300px' : vw < 1024 ? '450px' : '600px';
  }
  window.addEventListener('resize', updateVideoHeight);
  updateVideoHeight();

  // Initialize
  playVideo(0);
});
</script>
<?php endif; ?>

  <!-- Standalone Search Section -->
  <div id="standalone-search-container" style="padding: 40px 0; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); text-align: center; margin: 20px 0;">
    <h2 style="margin-bottom: 20px; font-size: 2rem; color: #333;">Find Your Perfect Device</h2>
    <p style="margin-bottom: 30px; color: #666; max-width: 800px; margin-left: auto; margin-right: auto;">
      Search our extensive catalog of smartphones, tablets, laptops, and accessories to find exactly what you need.
    </p>
    <div class="search-container" style="max-width: 600px; margin: 0 auto; position: relative; z-index: 1000;">
      <input type="text" id="standaloneSearchInput" placeholder="Search products by name, brand, or model..." style="width: 100%; padding: 15px 48px 15px 20px; border: 1px solid #ddd; border-radius: 30px; font-size: 1.1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
      <button id="standaloneSearchButton" style="position: absolute; top: 50%; right: 15px; transform: translateY(-50%); background: transparent; border: none; cursor: pointer; color: #007bff; font-size: 1.2rem;">
        <i class="fas fa-search"></i>
      </button>
      <div id="standaloneSearchSuggestions" class="search-suggestions" style="display: none; position: absolute; top: 60px; left: 0; right: 0; background: #fff; border-radius: 12px; box-shadow: 0 6px 24px rgba(0,0,0,0.12); z-index: 1001; max-height: 400px; overflow-y: auto;"></div>
    </div>
  </div>

  <div id="container2">
    <h2>OUR COLLECTIONS</h2>
    <p>Explore our diverse range of collections featuring the latest accessories, gadgets, and tech essentials curated to meet your needs.</p>
    <div id="productGrid2">
      <?php include 'kioskcollections.php'; ?>
    </div>
  </div>

  <div id="container3">
    <h2>
      OUR PRODUCTS
    </h2>
    <?php
      // Only show category buttons except "PC Set"
      include_once 'category_buttons.php';
      if (function_exists('render_category_buttons')) {
        // If your category_buttons.php uses a function, call it with a filter
        render_category_buttons(function($category) {
          return strtolower($category['name']) !== 'pc set';
        });
      } else {
        // If category_buttons.php outputs HTML directly, you need to edit that file:
        // - Add a check to skip "PC Set" category when rendering buttons.
      }
    ?>
    <div class="carousel-container">
      <button id="carouselPrev" class="carousel-btn carousel-prev" aria-label="Previous products">
        <i class="fas fa-chevron-left"></i>
      </button>
      <div id="productGrid" aria-live="polite" aria-atomic="true">
        <div class="loading-spinner-container">
          <div class="loading-spinner"></div>
        </div>
      </div>
      <button id="carouselNext" class="carousel-btn carousel-next" aria-label="Next products">
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>
  </div>

  <?php include 'kioskmodals.php'; ?>
<script>
// Customer Care Modal Logic
document.addEventListener('DOMContentLoaded', function() {
  // Open Customer Care modal from header link
  const customerCareLink = document.getElementById('customerCareLink');
  const careModals = [
    'contactUsModal',
    'howToOrderModal',
    'returnsRefundsModal',
    'warrantyModal'
  ];
  function closeAllCareModals() {
    careModals.forEach(id => {
      const modal = document.getElementById(id);
      if (modal) {
        modal.classList.remove('show', 'customer-care-active');
        modal.setAttribute('aria-hidden', 'true');
      }
    });
  }
  // Open the Contact Us modal by default
  if (customerCareLink) {
    customerCareLink.addEventListener('click', function(e) {
      e.preventDefault();
      closeAllCareModals();
      const modal = document.getElementById('contactUsModal');
      if (modal) {
        modal.classList.add('show', 'customer-care-active');
        modal.setAttribute('aria-hidden', 'false');
        modal.focus();
      }
    });
  }
  // Add close button logic for all care modals
  careModals.forEach(id => {
    const modal = document.getElementById(id);
    if (modal) {
      const closeBtn = modal.querySelector('.care-modal-close, .close');
      if (closeBtn) {
        closeBtn.addEventListener('click', function() {
          modal.classList.remove('show', 'customer-care-active');
          modal.setAttribute('aria-hidden', 'true');
        });
      }
      // Close modal on outside click
      modal.addEventListener('click', function(e) {
        if (e.target === modal) {
          modal.classList.remove('show', 'customer-care-active');
          modal.setAttribute('aria-hidden', 'true');
        }
      });
      // Trap focus inside modal for accessibility
      modal.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          modal.classList.remove('show', 'customer-care-active');
          modal.setAttribute('aria-hidden', 'true');
        }
      });
    }
  });
});
</script>

  <div id="container4">
    <footer>
      <p>&copy; <?php echo date("Y"); ?> BISLIG iCENTER. All rights reserved.</p>
      <p>
        Contact us: support@bisligicenter.com | 0976 003 5417
      </p>
       
      <div class="footer-icons">
        <a href="https://www.facebook.com/bisligicenter" target="_blank" title="Facebook">
          <i class="fab fa-facebook-f"></i>
        </a>
        <a href="javascript:void(0);" id="callIcon" title="Call Us">
          <i class="fas fa-phone"></i>
        </a>
        <a href="https://maps.app.goo.gl/jfV82bbHNnCWb3jXA" target="_blank" title="Google Map">
          <i class="fas fa-map-marker-alt"></i>
        </a>
      </div>
    </footer>
  </div>

  <!-- Call Modal -->
  <div id="callModal" class="modal">
    <div class="modal-content"> 
      <span id="closeCallModal" class="close">&times;</span>
      <h3>Contact Number</h3>
      <p>Ernie E. Mag-aso</p>
      <p>0976 003 5417</p>
    </div>
  </div>

  <?php include 'customer_chat_widget.php'; ?>
  <script src="kiosk.js"></script>
</body>
</html>
