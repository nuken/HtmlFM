<?php
// Define the directory containing the HTML files
$html_directory = 'Files/'; // Replace with your folder path

// Get a list of all HTML files in the directory
$html_files = glob($html_directory . '*.html'); // Adjust extension if needed (e.g., '*.htm')
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTML File Cards</title>
	<link href="css/card.css" rel="stylesheet">
</head>
<body>

    <header>
        Test Site
    </header>

    <?php
    // Check if any HTML files were found
    if (!empty($html_files)) {
        // Loop through each HTML file found
        foreach ($html_files as $file) {
            // Read the content of the HTML file
            $file_content = file_get_contents($file);

            // Display the content within a card
            echo '<div class="card">';
            echo $file_content;
            echo '</div>';
        }
    } else {
        echo '<p>No HTML files found in the specified directory.</p>';
    }
    ?>

</body>
</html>