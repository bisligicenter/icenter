<?php
// add_category.php
// Simple page to add a category into the `category` table (field: category_name)

require_once __DIR__ . '/db.php';

$errorMessage = '';
$successMessage = '';
$fetchedCategories = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';
    // Force uppercase server-side
    if ($categoryName !== '') {
        $categoryName = mb_strtoupper($categoryName, 'UTF-8');
    }

    if ($categoryName === '') {
        $errorMessage = 'Category name is required.';
    } elseif (mb_strlen($categoryName) > 255) {
        $errorMessage = 'Category name must be 255 characters or fewer.';
    } else {
        try {
            $db = getConnection();
            if (!$db) {
                $errorMessage = 'Database connection is not available.';
            } else {
                // Optional: prevent exact duplicates (case-insensitive)
                $checkStmt = $db->prepare('SELECT COUNT(*) AS cnt FROM categories WHERE LOWER(category_name) = LOWER(:name)');
                $checkStmt->execute([':name' => $categoryName]);
                $exists = (int)$checkStmt->fetchColumn() > 0;

                if ($exists) {
                    $errorMessage = 'This category already exists.';
                } else {
                    $stmt = $db->prepare('INSERT INTO categories (category_name) VALUES (:name)');
                    $stmt->execute([':name' => $categoryName]);
                    $successMessage = 'Category added successfully.';
                }
            }
        } catch (Throwable $e) {
            error_log('add_category.php error: ' . $e->getMessage());
            $errorMessage = 'An unexpected error occurred while saving the category.';
        }
    }
}

// Fetch all categories to display
try {
    $db = getConnection();
    if ($db) {
        $stmt = $db->query('SELECT category_name FROM categories ORDER BY category_name ASC');
        $fetchedCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
} catch (Throwable $e) {
    error_log('add_category.php fetch categories error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Add Category</title>
	<script src="https://cdn.tailwindcss.com/3.4.16"></script>
	<script>
		tailwind.config = {
			theme: {
				extend: {
					colors: { primary: "#1a1a1a", secondary: "#404040" },
					borderRadius: {
						none: "0px",
						sm: "4px",
						DEFAULT: "8px",
						md: "12px",
						lg: "16px",
						xl: "20px",
						"2xl": "24px",
						"3xl": "32px",
						full: "9999px",
						button: "8px",
					},
				},
			},
		};
	</script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
	<script>
		function validateForm() {
			var input = document.getElementById('category_name');
			if (!input.value.trim()) {
				alert('Please enter a category name.');
				input.focus();
				return false;
			}
			return true;
		}
	</script>
	</head>
<body class="min-h-screen" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
	<div class="min-h-screen flex flex-col">
		<!-- Top bar with Back button -->
		<header class="bg-gradient-to-r from-[#1a1a1a] to-[#2d2d2d] shadow-lg border-b border-white/10">
			<div class="max-w-4xl mx-auto w-full px-4 py-4 flex items-center justify-between">
				<a href="admin.php" class="inline-flex items-center gap-2 text-white font-semibold px-4 py-2 rounded-xl border border-white/20 bg-white/10 hover:bg-white/20 transition">
					<i class="ri-arrow-left-line"></i>
					<span>Back to Dashboard</span>
				</a>
				<h1 class="text-white text-lg font-bold">Add Category</h1>
				<span></span>
			</div>
		</header>

		<!-- Content -->
		<main class="flex-1 flex items-center justify-center px-4 py-10">
			<div class="w-full max-w-xl">
				<div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
					<div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
						<h2 class="text-xl font-semibold text-gray-800">Create a new category</h2>
						<p class="text-sm text-gray-500 mt-1">Enter a unique name to add to your categories list.</p>
					</div>
					<div class="p-6">
						<?php if ($errorMessage !== ''): ?>
							<div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-700 px-4 py-3">
								<?php echo htmlspecialchars($errorMessage); ?>
							</div>
						<?php endif; ?>

						<?php if ($successMessage !== ''): ?>
							<div class="mb-4 rounded-xl border border-green-200 bg-green-50 text-green-700 px-4 py-3">
								<?php echo htmlspecialchars($successMessage); ?>
							</div>
						<?php endif; ?>

						<form method="post" action="" onsubmit="return validateForm();" class="space-y-4">
                            <label for="category_name" class="block text-sm font-medium text-gray-700">Category Name</label>
                            <input type="text" id="category_name" name="category_name" maxlength="255" placeholder="ENTER CATEGORY" class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent" oninput="this.value=this.value.toUpperCase();" />

							<div class="pt-2">
								<button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl text-white font-semibold bg-gradient-to-r from-gray-800 to-black hover:from-gray-900 hover:to-gray-800 transition">
									<i class="ri-price-tag-3-line"></i>
									<span>Add Category</span>
								</button>
							</div>
						</form>
                        
					</div>
				</div>
			</div>
		</main>
	</div>
</body>
</html>


