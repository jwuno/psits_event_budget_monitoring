<?php
// === Add new roles and folders for PSITS Event & Budget Monitoring ===
// Author: JHCSC Pagadian Annex - BSIT PSITS

$base = __DIR__; // use current folder

$newFolders = [
    "$base/pages/adviser",
    "$base/pages/pio",
    "$base/pages/president",
    "$base/pages/secretary",
    "$base/pages/treasurer",
    "$base/pages/student"
];

// Create each folder if it doesn’t exist
foreach ($newFolders as $folder) {
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
        echo "✅ Folder created: $folder<br>";
    } else {
        echo "⚠️ Folder already exists: $folder<br>";
    }
}

// === Create dashboard.php file for each role ===
$roles = ['adviser','pio','president','secretary','treasurer','student'];

foreach ($roles as $role) {
    $filePath = "$base/pages/$role/dashboard.php";
    if (!file_exists($filePath)) {
        $roleCap = ucfirst($role);
        $content = "<?php include_once('../../includes/header.php'); ?>\n" .
                   "<h2>$roleCap Dashboard</h2>\n" .
                   "<p>Welcome, $roleCap! This is your dashboard.</p>\n" .
                   "<?php include_once('../../includes/footer.php'); ?>";
        file_put_contents($filePath, $content);
        echo "📄 Created: $filePath<br>";
    } else {
        echo "⚠️ Already exists: $filePath<br>";
    }
}

echo "<hr><strong>✅ Role setup complete!</strong>";
?>
