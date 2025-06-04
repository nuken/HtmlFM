<?php
session_start();
require_once __DIR__ . '/../private/config.php'; // Assumes config.php defines FILES_DIR

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$message = ''; // To store messages for the user (success or error)
$current_file_path = null;
$file_content = '';

// Define the target directory for file management.
// This assumes FILES_DIR is defined in your config.php, e.g., define('FILES_DIR', __DIR__ . '/Files');
$targetDirectory = FILES_DIR;

// Ensure IMAGES_DIR is defined and create it if it doesn't exist
if (!defined('IMAGES_DIR')) {
    // Fallback if not defined in config.php, or define it there for better practice.
    define('IMAGES_DIR', __DIR__ . '/images'); // Adjust this path as necessary
}

// Create the images directory if it doesn't exist
if (!is_dir(IMAGES_DIR)) {
    mkdir(IMAGES_DIR, 0755, true);
}


// --- Helper Functions for File Management ---

/**
 * Validates if a target file path is safely within a designated base directory.
 * @param string $baseDirectory The secure base directory.
 * @param string $fileName The user-supplied filename (potentially malicious).
 * @return string|false The full validated and safe file path, or false if validation fails.
 */
function validateAndGetSafePath($baseDirectory, $fileName) {
    $sanitizedFileName = basename($fileName); //
    $fullPath = rtrim($baseDirectory, '/\\') . DIRECTORY_SEPARATOR . $sanitizedFileName;
    $normalizedFullPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fullPath);
    $normalizedBaseDirectory = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, rtrim($baseDirectory, '/\\') . DIRECTORY_SEPARATOR);

    if (strpos($normalizedFullPath, $normalizedBaseDirectory) !== 0) {
        return false;
    }

    // For read/delete/edit, it's good to check if the file exists and is a file.
    if (!file_exists($normalizedFullPath) || !is_file($normalizedFullPath)) {
        return false;
    }

    return $normalizedFullPath;
}

// A slightly modified version for creation, as the file won't exist initially.
function validateAndGetSafePathForCreation($baseDirectory, $fileName) {
    $sanitizedFileName = basename($fileName); //
    // Ensure the file name has an HTML extension for creation
    if (!preg_match('/\.(html|htm)$/i', $sanitizedFileName)) {
        $sanitizedFileName .= '.html'; //
    }
    $fullPath = rtrim($baseDirectory, '/\\') . DIRECTORY_SEPARATOR . $sanitizedFileName;
    $normalizedFullPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fullPath);
    $normalizedBaseDirectory = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, rtrim($baseDirectory, '/\\') . DIRECTORY_SEPARATOR);

    if (strpos($normalizedFullPath, $normalizedBaseDirectory) !== 0) {
        return false;
    }
    // No file_exists check needed here, as we are creating it.
    return $normalizedFullPath;
}


/**
 * Scans the target directory for HTML files.
 * @param string $directory The directory to scan.
 * @return array An array of HTML file names.
 */
function scanHtmlFiles($directory) {
    $files = [];
    if (is_dir($directory)) {
        $items = scandir($directory);
        foreach ($items as $item) {
            // Ignore current and parent directory entries
            if ($item === '.' || $item === '..') {
                continue;
            }
            // Check if it's a file and has a .html or .htm extension (case-insensitive)
            $filePath = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_file($filePath) && (preg_match('/\.html$/i', $item) || preg_match('/\.htm$/i', $item))) {
                $files[] = $item;
            }
        }
    }
    return $files;
}

/**
 * Creates a new HTML file.
 * @param string $directory The directory to create the file in.
 * @param string $fileName The name of the file to create.
 * @return string A message indicating success or failure.
 */
function createFile($directory, $fileName) {
    // Sanitize filename to prevent directory traversal or other malicious input
    $fileName = basename($fileName); //

    // Ensure the file name has an HTML extension
    if (!preg_match('/\.(html|htm)$/i', $fileName)) {
        $fileName .= '.html'; //
    }

    $filePath = validateAndGetSafePathForCreation($directory, $fileName); // Use the creation-specific validator
    if ($filePath === false) {
        return "<p class='error'>Security Error: Attempt to create file outside designated directory or invalid filename.</p>";
    }

    if (file_exists($filePath)) { //
        return "<p class='error'>Error: File '{$fileName}' already exists!</p>";
    }

    // Attempt to create the file with some basic HTML content
    $initialContent = "<div>\n<title>{$fileName}</title>\n<h1>Welcome to {$fileName}</h1>\n<p>This is a newly created HTML file.</p>\n</div>"; //
    if (file_put_contents($filePath, $initialContent) !== false) { //
        return "<p class='success'>File '{$fileName}' created successfully!</p>";
    } else {
        return "<p class='error'>Error: Could not create file '{$fileName}'. Check directory permissions.</p>"; //
    }
}

/**
 * Deletes an existing HTML file.
 * @param string $directory The directory where the file is located.
 * @param string $fileName The name of the file to delete.
 * @return string A message indicating success or failure.
 */
function deleteFile($directory, $fileName) {
    // Sanitize filename
    $fileName = basename($fileName); //
    $filePath = validateAndGetSafePath($directory, $fileName); // Use the general validator

    if ($filePath === false) {
        return "<p class='error'>Security Error: Attempt to delete file outside designated directory or invalid file.</p>";
    }

    // The validateAndGetSafePath already checks file_exists and is_file,
    // but the specific HTML file check is still good to keep here.
    if (!preg_match('/\.(html|htm)$/i', $fileName)) {
        return "<p class='error'>Error: Only HTML files can be deleted through this interface.</p>"; //
    }

    if (unlink($filePath)) { //
        return "<p class='success'>File '{$fileName}' deleted successfully!</p>";
    } else {
        return "<p class='error'>Error: Could not delete file '{$fileName}'. Check directory permissions.</p>"; //
    }
}

/**
 * Renames an existing HTML file.
 * @param string $directory The directory where the file is located.
 * @param string $oldFileName The current name of the file.
 * @param string $newFileName The new name for the file.
 * @return string A message indicating success or failure.
 */
function renameFile($directory, $oldFileName, $newFileName) {
    // Sanitize filenames
    $oldFileName = basename($oldFileName); //
    $newFileName = basename($newFileName); //

    $oldFilePath = validateAndGetSafePath($directory, $oldFileName);
    if ($oldFilePath === false) {
        return "<p class='error'>Security Error: Invalid original file for rename or attempt to access file outside designated directory.</p>";
    }

    // Ensure the new file name has an HTML extension
    if (!preg_match('/\.(html|htm)$/i', $newFileName)) {
        $newFileName .= '.html'; //
    }
    $newFilePath = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $newFileName; // Construct new path for validation

    // Validate the new path against the base directory
    $normalizedNewFilePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $newFilePath);
    $normalizedBaseDirectory = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, rtrim($directory, '/\\') . DIRECTORY_SEPARATOR);

    if (strpos($normalizedNewFilePath, $normalizedBaseDirectory) !== 0) {
        return "<p class='error'>Security Error: Attempt to rename file outside designated directory.</p>";
    }

    // Basic check to ensure it's an HTML file before renaming
    if (!preg_match('/\.(html|htm)$/i', $oldFileName)) {
        return "<p class='error'>Error: Only HTML files can be renamed through this interface.</p>"; //
    }

    if (file_exists($newFilePath)) { //
        return "<p class='error'>Error: A file named '{$newFileName}' already exists!</p>";
    }

    if (rename($oldFilePath, $newFilePath)) { //
        return "<p class='success'>File '{$oldFileName}' renamed to '{$newFileName}' successfully!</p>";
    } else {
        return "<p class='error'>Error: Could not rename file '{$oldFileName}' to '{$newFileName}'. Check directory permissions.</p>"; //
    }
}

/**
 * Handles image uploads.
 * @param array $file The $_FILES array for the uploaded file.
 * @param string $targetDirectory The directory to upload the image to.
 * @return string A message indicating success or failure.
 */
function handleImageUpload($file, $targetDirectory) {
    if (!isset($file['name']) || $file['error'] !== UPLOAD_ERR_OK) { //
        return "<p class='error'>Error: No file uploaded or an upload error occurred.</p>"; //
    }

    $fileName = basename($file['name']); //
    $targetFilePath = $targetDirectory . DIRECTORY_SEPARATOR . $fileName; //
    $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION)); //

    // Check if image file is a actual image or fake image
    $check = getimagesize($file['tmp_name']); //
    if ($check === false) {
        return "<p class='error'>Error: File is not an image.</p>"; //
    }

    // Allow certain file formats
    $allowedTypes = ['jpg', 'png', 'jpeg', 'gif', 'webp', 'svg']; //
    if (!in_array($imageFileType, $allowedTypes)) { //
        return "<p class='error'>Error: Sorry, only JPG, JPEG, PNG, GIF, WEBP, & SVG files are allowed for upload.</p>"; //
    }

    // Check file size (e.g., 5MB limit)
    if ($file['size'] > 5 * 1024 * 1024) { // 5 MB in bytes
        return "<p class='error'>Error: Sorry, your file is too large (max 5MB).</p>"; //
    }

    // Check if file already exists
    if (file_exists($targetFilePath)) { //
        return "<p class='error'>Error: Sorry, file '{$fileName}' already exists.</p>"; //
    }

    // Validate that the path is within the allowed directory
    // For uploads, we need to check the constructed path to ensure it doesn't try to escape
    $normalizedTargetFilePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $targetFilePath);
    $normalizedTargetDirectory = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, rtrim($targetDirectory, '/\\') . DIRECTORY_SEPARATOR);

    if (strpos($normalizedTargetFilePath, $normalizedTargetDirectory) !== 0) {
        return "<p class='error'>Security Error: Attempt to upload file outside designated directory.</p>";
    }

    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) { //
        return "<p class='success'>The file '{$fileName}' has been uploaded.</p>"; //
    } else {
        return "<p class='error'>Error: There was an error uploading your file. Check directory permissions.</p>"; //
    }
}


// --- Handle File Editing and Management Form Submissions ---

// Handle file save (from original edit.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_path']) && isset($_POST['content'])) { //
    $file_to_save = basename($_POST['file_path']); // Get filename without path
    // $save_path = $targetDirectory . DIRECTORY_SEPARATOR . $file_to_save; // Not directly used for validation

    $safe_save_path = validateAndGetSafePath($targetDirectory, $file_to_save); // Use the validator
    if ($safe_save_path === false) {
        $message = 'Security Error: Attempt to save file outside designated directory.'; //
    } else {
        $content = $_POST['content']; //
        if (file_put_contents($safe_save_path, $content) !== false) { //
            $message = 'File saved successfully!'; //
            // Update file content in the editor after saving
            $file_content = $content; //
        } else {
            $message = 'Error: Could not save file. Check directory permissions.'; //
        }
    }
}

// Handle file management actions (create, delete, rename)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) { //
    switch ($_POST['action']) { //
        case 'create': //
            if (isset($_POST['new_file_name']) && !empty(trim($_POST['new_file_name']))) { //
                $message = createFile($targetDirectory, trim($_POST['new_file_name'])); //
            } else {
                $message = "<p class='error'>Please provide a name for the new file.</p>"; //
            }
            break;

        case 'delete': //
            if (isset($_POST['file_to_delete']) && !empty($_POST['file_to_delete'])) { //
                $message = deleteFile($targetDirectory, $_POST['file_to_delete']); //
            } else {
                $message = "<p class='error'>Please select a file to delete.</p>"; //
            }
            break;

        case 'rename': //
            if (isset($_POST['old_file_name']) && !empty($_POST['old_file_name']) && //
                isset($_POST['new_file_name_rename']) && !empty(trim($_POST['new_file_name_rename']))) { //
                $message = renameFile($targetDirectory, $_POST['old_file_name'], trim($_POST['new_file_name_rename'])); //
            } else {
                $message = "<p class='error'>Please select a file and provide a new name to rename.</p>"; //
            }
            break;
    }
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image']) && isset($_FILES['image_file'])) { //
    $message = handleImageUpload($_FILES['image_file'], IMAGES_DIR); //
}


// Sanitize and validate requested file for editing (from original edit.php)
if (isset($_GET['file'])) { //
    $requested_file = basename($_GET['file']); // Get filename without path
    // $current_file_path = $targetDirectory . DIRECTORY_SEPARATOR . $requested_file; // Not directly used for validation

    $safe_current_file_path = validateAndGetSafePath($targetDirectory, $requested_file);
    if ($safe_current_file_path === false) {
        $message = 'Warning: File not found or deleted or invalid path.'; //
        $current_file_path = null; // Invalidate the file path
    } else {
        $current_file_path = $safe_current_file_path; // Use the validated path
        $file_content = file_get_contents($current_file_path); //
        if ($file_content === false) { //
            $message = 'Error: Could not read file content.'; //
            $current_file_path = null; //
        }
    }
}

// Get the current list of HTML files
$htmlFiles = scanHtmlFiles($targetDirectory); //
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure HTML File Manager & Editor</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
	<link href="css/style.css" rel="stylesheet">
   <script src="js/tinymce/tinymce.min.js"></script>
    <script>
        tinymce.init({
    selector: '#editor',
    plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
    toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help | code',
    content_css: '//www.tiny.cloud/css/codepen.min.css',
   });
    </script>

</head>
<body>
   <div class="container">
    <div class="header-section">
        <h1 class="text-3xl">Secure HTML File Manager & Editor</h1>
        <nav>
            <span>Logged in as: <?php echo htmlspecialchars($_SESSION['username']); ?></span> |
            <a href="logout.php" class="logout-link">Logout</a>
        </nav>
    </div>

    <?php if ($message): ?>
        <div class="message-box <?php echo (strpos($message, 'Error') === 0 || strpos($message, 'Security Error') === 0) ? 'error' : 'success'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="grid-container">
        <div>
            <div class="form-group">
                <form action="" method="GET">
                    <h2 class="text-2xl">Existing HTML Files:</h2>
                    <?php if (empty($htmlFiles)): ?>
                        <p class="text-gray-600">No HTML files found in the directory.</p>
                    <?php else: ?>
                        <select name="file" id="html_file" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" onchange="this.form.submit()">
                            <option value="" disabled selected hidden>-- Select an HTML file --</option>
                            <?php foreach ($htmlFiles as $file): ?>
                                <option value="<?php echo urlencode($file); ?>" <?php echo (isset($_GET['file']) && $_GET['file'] == urlencode($file)) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($file); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-gray-600 text-sm mt-2">The page will refresh when you select a file.</p>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div>
            <?php if ($current_file_path): ?>
                <div class="editor-section">
                    <h2 class="text-2xl">Editing: <?php echo htmlspecialchars(basename($current_file_path)); ?></h2>

                    <div class="view-content">
                        <h3>Current File Content (Read-Only Preview):</h3>
                        <pre><?php echo htmlspecialchars($file_content); ?></pre>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="file_path" value="<?php echo htmlspecialchars(basename($current_file_path)); ?>">
                        <label for="editor" class="mt-4">Edit Content:</label>
                        <textarea id="editor" name="content"><?php echo htmlspecialchars($file_content); ?></textarea>
                        <div class="button-group text-right mt-4">
                            <button type="submit" class="btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            <?php elseif (isset($_GET['file']) && !$current_file_path): ?>
                <p class="text-red-600">Please select a valid file from the list above.</p>
            <?php else: ?>
                <p class="text-gray-600">Select an HTML file from the list to edit its content.</p>
            <?php endif; ?>
        </div>

        <div class="section-separator"></div>

        <div class="grid-two-columns">
            <div class="form-group">
                <h2 class="text-2xl">Create New HTML File:</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="create">
                    <label for="new_file_name">New File Name (e.g., mypage.html):</label>
                    <input type="text" id="new_file_name" name="new_file_name" placeholder="Enter file name" required class="mb-4">
                    <button type="submit" class="btn-primary">Create File</button>
                </form>
            </div>

            <div class="form-group">
                <h2 class="text-2xl">Delete HTML File:</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="delete">
                    <label for="file_to_delete">Select File to Delete:</label>
                    <select id="file_to_delete" name="file_to_delete" required class="mb-4">
                        <option value="" disabled selected hidden>-- Select a file --</option>
                        <?php foreach ($htmlFiles as $file): ?>
                            <option value="<?php echo htmlspecialchars($file); ?>"><?php echo htmlspecialchars($file); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-danger">Delete File</button>
                </form>
            </div>

            <div class="form-group">
                <h2 class="text-2xl">Rename HTML File:</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="rename">
                    <label for="old_file_name">Select File to Rename:</label>
                    <select id="old_file_name" name="old_file_name" required class="mb-4">
                        <option value="" disabled selected hidden>-- Select a file --</option>
                        <?php foreach ($htmlFiles as $file): ?>
                            <option value="<?php echo htmlspecialchars($file); ?>"><?php echo htmlspecialchars($file); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="new_file_name_rename">New File Name (e.g., newpage.html):</label>
                    <input type="text" id="new_file_name_rename" name="new_file_name_rename" placeholder="Enter new file name" required class="mb-4">
                    <button type="submit" class="btn-primary">Rename File</button>
                </form>
            </div>

            <div class="form-group image-uploader">
                <h2 class="text-2xl">Upload Image:</h2>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="upload_image" value="1">
                    <label for="image_file">Select Image File:</label>
                    <input type="file" id="image_file" name="image_file" accept=".jpg, .jpeg, .png, .gif, .webp, .svg" required class="mb-4">
                    <button type="submit" class="btn-primary">Upload Image</button>
                    <p class="text-gray-600 text-sm mt-2">Allowed types: JPG, JPEG, PNG, GIF, WEBP, SVG (Max 5MB)</p>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
