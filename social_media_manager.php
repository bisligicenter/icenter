<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Handle social media post creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $platform = $_POST['platform'];
    $content = trim($_POST['content']);
    $post_type = $_POST['post_type'];
    $scheduled_time = $_POST['scheduled_time'];
    $include_image = isset($_POST['include_image']);
    
    if (empty($content)) {
        $error = "Content is required.";
    } else {
        try {
            // Handle image upload if included
            $image_path = '';
            if ($include_image && isset($_FILES['post_image']) && $_FILES['post_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'social_media_images/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file = $_FILES['post_image'];
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
                
                if (!in_array($file['type'], $allowed_types)) {
                    throw new Exception('Invalid file type. Only JPG, JPEG, and PNG files are allowed.');
                }
                
                if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
                    throw new Exception('File size too large. Maximum size is 5MB.');
                }
                
                $filename = 'social_' . uniqid() . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                $upload_path = $upload_dir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    $image_path = $upload_path;
                }
            }
            
            // Save post to database
            $stmt = $conn->prepare("INSERT INTO social_media_posts (platform, content, post_type, image_path, scheduled_time, status, created_by, created_at) VALUES (?, ?, ?, ?, ?, 'scheduled', ?, NOW())");
            $stmt->execute([$platform, $content, $post_type, $image_path, $scheduled_time, $_SESSION['username'] ?? 'admin']);
            
            $success = "Social media post scheduled successfully!";
            
        } catch (Exception $e) {
            $error = "Error creating post: " . $e->getMessage();
        }
    }
}

// Get recent social media posts
$stmt = $conn->query("SELECT * FROM social_media_posts ORDER BY created_at DESC LIMIT 10");
$recent_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media Manager - Admin</title>
    <link rel="icon" type="image/png" href="images/iCenter.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" />
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
        .btn { transition: all 0.3s ease; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .platform-icon { width: 24px; height: 24px; }
    </style>
</head>
<body class="min-h-screen">
    <header class="bg-gradient-to-r from-[#1a1a1a] to-[#2d2d2d] shadow-lg border-b border-white/10 sticky top-0 z-20 backdrop-blur-sm">
        <div class="flex justify-between items-center px-4 lg:px-8 py-4 lg:py-6">
            <div class="flex items-center space-x-3 lg:space-x-6">
                <img src="images/iCenter.png" alt="Logo" class="h-12 lg:h-20 w-auto border-2 border-white rounded-lg shadow-lg" />
                <span class="text-white text-lg font-semibold">Social Media Manager</span>
            </div>
        </div>
    </header>

    <div class="container mx-auto p-6 lg:p-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Social Media Manager</h1>
            <a href="admin.php" class="btn bg-white text-gray-700 font-semibold py-2 px-4 rounded-lg shadow-sm border border-gray-300 hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>

        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <i class="ri-check-circle-line mr-2"></i><?= $success ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <i class="ri-error-warning-line mr-2"></i><?= $error ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Post Creation Form -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div class="flex items-center mb-6">
                    <i class="ri-share-line mr-3 text-2xl text-purple-600"></i>
                    <h2 class="text-2xl font-bold text-gray-800">Create Social Media Post</h2>
                </div>

                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Platform</label>
                        <select name="platform" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="facebook">Facebook</option>
                            <option value="instagram">Instagram</option>
                            <option value="twitter">Twitter/X</option>
                            <option value="tiktok">TikTok</option>
                            <option value="youtube">YouTube</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Post Type</label>
                        <select name="post_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="sale">Sale Announcement</option>
                            <option value="new_product">New Product Launch</option>
                            <option value="promotion">Special Promotion</option>
                            <option value="update">Store Update</option>
                            <option value="general">General Post</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                        <textarea name="content" rows="6" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Write your social media post content..."></textarea>
                        <div class="text-xs text-gray-500 mt-1">
                            <span id="char-count">0</span> characters
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Time</label>
                        <input type="datetime-local" name="scheduled_time" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="include_image" id="include_image" class="mr-2">
                        <label for="include_image" class="text-sm text-gray-700">Include image</label>
                    </div>

                    <div id="image-upload" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Post Image</label>
                        <input type="file" name="post_image" accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-800 mb-2">Content Templates</h3>
                        <div class="space-y-2 text-sm">
                            <button type="button" class="template-btn text-blue-600 hover:text-blue-800" data-template="🎉 BIG SALE ALERT! 🎉\n\nGet up to 50% off on selected iPhones at Bislig iCenter!\n\n📱 iPhone 11 - Now ₱25,000\n📱 iPhone 12 - Now ₱35,000\n📱 iPhone 13 - Now ₱45,000\n\n📍 Visit us at Bislig City\n📞 Contact: 0976 003 5417\n\n#BisligiCenter #iPhoneSale #Mindanao">Sale Template</button><br>
                            <button type="button" class="template-btn text-blue-600 hover:text-blue-800" data-template="📱 NEW ARRIVAL! 📱\n\niPhone 16 Pro is now available at Bislig iCenter!\n\n✨ Latest features\n✨ Premium quality\n✨ Best price in Mindanao\n\nBe the first to get yours! Limited stock available.\n\n📍 Bislig City\n📞 0976 003 5417\n\n#iPhone16Pro #NewArrival #BisligiCenter">New Product Template</button><br>
                            <button type="button" class="template-btn text-blue-600 hover:text-blue-800" data-template="💝 SPECIAL OFFER! 💝\n\nFree screen protector with any iPhone purchase!\n\nValid until this weekend only.\n\n📍 Bislig iCenter\n📞 0976 003 5417\n\n#SpecialOffer #FreeScreenProtector #BisligiCenter">Promotion Template</button>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-purple-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-purple-700 transition-colors">
                        <i class="ri-schedule-line mr-2"></i>Schedule Post
                    </button>
                </form>
            </div>

            <!-- Recent Posts -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <div class="flex items-center mb-6">
                    <i class="ri-history-line mr-3 text-2xl text-purple-600"></i>
                    <h2 class="text-2xl font-bold text-gray-800">Recent Posts</h2>
                </div>

                <div class="space-y-4">
                    <?php foreach ($recent_posts as $post): ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center">
                                    <span class="platform-icon mr-2">
                                        <?php
                                        $platform_icons = [
                                            'facebook' => 'ri-facebook-circle-fill',
                                            'instagram' => 'ri-instagram-line',
                                            'twitter' => 'ri-twitter-fill',
                                            'tiktok' => 'ri-tiktok-fill',
                                            'youtube' => 'ri-youtube-fill'
                                        ];
                                        $icon_class = $platform_icons[$post['platform']] ?? 'ri-share-line';
                                        ?>
                                        <i class="<?= $icon_class ?> text-lg"></i>
                                    </span>
                                    <h3 class="font-semibold text-gray-800"><?= ucfirst($post['platform']) ?></h3>
                                </div>
                                <span class="text-xs text-gray-500"><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                            </div>
                            <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($post['content']) ?></p>
                            <div class="flex justify-between text-xs text-gray-500">
                                <span>Type: <?= ucfirst(str_replace('_', ' ', $post['post_type'])) ?></span>
                                <span class="capitalize"><?= $post['status'] ?></span>
                            </div>
                            <?php if ($post['image_path']): ?>
                                <div class="mt-2">
                                    <img src="<?= $post['image_path'] ?>" alt="Post image" class="w-16 h-16 object-cover rounded">
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Character counter
        const contentTextarea = document.querySelector('textarea[name="content"]');
        const charCount = document.getElementById('char-count');
        
        contentTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
        
        // Image upload toggle
        const includeImageCheckbox = document.getElementById('include_image');
        const imageUpload = document.getElementById('image-upload');
        
        includeImageCheckbox.addEventListener('change', function() {
            if (this.checked) {
                imageUpload.classList.remove('hidden');
            } else {
                imageUpload.classList.add('hidden');
            }
        });
        
        // Template buttons
        document.querySelectorAll('.template-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                contentTextarea.value = this.dataset.template;
                charCount.textContent = contentTextarea.value.length;
            });
        });
    </script>
</body>
</html> 