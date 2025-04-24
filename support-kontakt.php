<?php
// Include the necessary functions for database connection and user check
require_once 'func.php';

// Redirect to login page if user is not logged in
if (!getUserId()) {
    header("Location: logga-in.php");
    exit();
}

// Initialize variables to handle form submission success or failure
$success = false;
$error = false;

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Create a database connection
    $conn = getDbConnection();

    // Get the user ID and sanitize input values
    $userId = getUserId();
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $image = null;

    // Handle image upload if user selects a file
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
        // Define allowed image types
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES["image"]["type"];
        $fileTmpName = $_FILES["image"]["tmp_name"];
        $fileName = basename($_FILES["image"]["name"]);

        // Check if the uploaded image is of a valid type
        if (in_array($fileType, $allowedTypes)) {
            // Create the upload directory if it does not exist
            $uploadDir = "uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            // Generate a unique path for the image file
            $targetPath = $uploadDir . time() . "_" . $fileName;
            // Move the uploaded file to the target directory
            if (move_uploaded_file($fileTmpName, $targetPath)) {
                $image = $targetPath; // Save the image path if upload is successful
            }
        }
    }

    // Check if title and description are not empty before inserting into the database
    if (!empty($title) && !empty($description)) {
        // Prepare and execute the insert query to save the ticket information
        $stmt = $conn->prepare("INSERT INTO ticketinfo (user_id, title, description, image, status) VALUES (?, ?, ?, ?, 'open')");
        $stmt->bind_param("isss", $userId, $title, $description, $image);

        // Check if the query executed successfully
        if ($stmt->execute()) {
            $success = true; // Set success flag if query is successful
        } else {
            $error = true; // Set error flag if there was an issue
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    } else {
        // Set error flag if title or description is missing
        $error = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Support Ticket - SkySurprise</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to the CSS file -->
</head>
<body>

<!-- Header section with navigation links -->
<div class="header">
    <div class="headerleft">
        <a href="dashboard.php">My journey</a>
        <a href="om-oss.php">About us</a>
    </div>
    <div class="headermiddle">
        <div class="logo">
            <a href="main.php"><img src="bilder/skysurpriselogo.png" alt="logo"></a>
        </div>
    </div>
    <div class="headerright">
        <a href="main.php">Home</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logga-ut.php">Log Out</a>
        <?php else: ?>
            <a href="logga-in.php">Log In</a>
        <?php endif; ?>
    </div>
</div>

<!-- Main form container for submitting a support ticket -->
<div class="kontakt-formcontainer">
    <h2>Submit a Support Ticket</h2>

    <!-- Display success or error messages based on the ticket submission outcome -->
    <?php if ($success): ?>
        <p class="success">Your ticket has been submitted successfully.</p>
    <?php elseif ($error): ?>
        <p class="error">There was a problem submitting your ticket. Make sure all fields are filled in.</p>
    <?php endif; ?>

    <!-- Support ticket submission form -->
    <form method="post" enctype="multipart/form-data">
        <label for="title">Title:</label><br>
        <input type="text" id="title" name="title" maxlength="60" required><br><br>

        <label for="description">Description:</label><br>
        <textarea id="description" name="description" rows="6" required></textarea><br><br>

        <label for="image">Attach Image (optional):</label><br>
        <input type="file" name="image" id="image" accept="image/*"><br><br>

        <button type="submit">Submit Ticket</button>
    </form>
</div>

<!-- Footer section with contact details and company info -->
<div class="footer">
    <div class="footerinfo">
        <div class="kortinfo">
            <h2>SkySurprise</h2>
            <p>Pack your bags. We'll handle the rest</p>
        </div>
        <div class="foretagsinfo">
            <h3>Contact Us</h3>
            <p>Email: <a href="mailto:contact@skysurprise.com">contact@skysurprise.com</a></p>
            <p>Phone: <a href="tel:+46723456789">+46 723456789</a></p>
            <p>Address: <a href="#">Mysteriegatan 7, 111 45 Stockholm</a></p>
        </div>
        <div class="loggafooter">
            <img src="bilder/skysurpriselogo.png" alt="Company Logo">
        </div>
    </div>
    <div class="botten">
        <p>&copy; 2025 SkySurprise. All rights reserved.</p>
    </div>
</div>

</body>
</html>
