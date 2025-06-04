<?php
/**
 * Configuration file for the Web-Based HTML File Manager & Editor.
 *
 * This file defines essential settings for the application, including
 * authentication credentials and directory paths for storing files and images.
 *
 * It is crucial to keep this file OUTSIDE your public webroot for security.
 */

// --- Authentication Settings ---

/**
 * Define the username for accessing the file manager.
 * Replace 'your_secure_username' with your desired username.
 * Example: define('USERNAME', 'admin');
 */
define('USERNAME', 'your_secure_username');

/**
 * Define the hashed password for accessing the file manager.
 * Replace 'your_strong_password' with a strong, unique password.
 * The password_hash() function securely hashes the password using a strong, default algorithm.
 * DO NOT directly enter a plain-text password here.
 * Example: define('PASSWORD_HASH', password_hash('MySuperSecretPassword123!', PASSWORD_DEFAULT));
 */
define('PASSWORD_HASH', password_hash('your_strong_password', PASSWORD_DEFAULT));


// --- Directory Path Settings ---

/**
 * Define the absolute path to the directory where HTML files will be stored.
 * __DIR__ refers to the directory of the current file (private/).
 * '/../' navigates up one level from 'private/' to the server root.
 * 'public_html/Files' then points to the 'Files' directory within 'public_html'.
 *
 * IMPORTANT: You may need to change 'public_html' to match your server's actual
 * webroot directory name (e.g., 'htdocs', 'www', 'html', 'public').
 *
 * If you change the 'Files' folder name or its location relative to 'public_html',
 * you MUST also update the corresponding path in 'index.php' to ensure the
 * application can locate the files correctly.
 * Example: define('FILES_DIR', __DIR__ . '/../htdocs/MyHtmlDocuments');
 */
define('FILES_DIR', __DIR__ . '/../public_html/Files');

/**
 * Define the absolute path to the directory where uploaded images will be stored.
 * Similar to FILES_DIR, adjust 'public_html' if your webroot has a different name.
 * Example: define('IMAGES_DIR', __DIR__ . '/../www/assets/images');
 */
define('IMAGES_DIR', __DIR__ . '/../public_html/images');

?>