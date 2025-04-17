<?php
require_once 'func.php';

// Redirect to login if not logged in
if (!getUserId()) {
    header("Location: logga-in.php");
    exit();
}

$success = false;
$error = false;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn = getDbConnection();

    $userId = getUserId();
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);

    if (!empty($title) && !empty($description)) {
        $stmt = $conn->prepare("INSERT INTO ticketinfo (user_id, title, description, status) VALUES (?, ?, ?, 'open')");
        $stmt->bind_param("iss", $userId, $title, $description);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = true;
        }

        $stmt->close();
        $conn->close();
    } else {
        $error = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Support Ticket - SkySurprise</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

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

<div class="kontakt-formcontainer">
    <h2>Submit a Support Ticket</h2>

    <?php if ($success): ?>
        <p class="success">Your ticket has been submitted successfully.</p>
    <?php elseif ($error): ?>
        <p class="error">There was a problem submitting your ticket. Make sure all fields are filled in.</p>
    <?php endif; ?>

    <form method="post">
        <label for="title">Title:</label><br>
        <input type="text" id="title" name="title" maxlength="60" required><br><br>

        <label for="description">Description:</label><br>
        <textarea id="description" name="description" rows="6" required></textarea><br><br>

        <button type="submit">Submit Ticket</button>
    </form>
</div>

<div class="footer">
    <div class="footerinfo">
        <div class="kortinfo">
            <h2>SkySurprise</h2>
            <p>Pack your bags. We'll handle rest</p>
        </div>
        <div class="foretagsinfo">
            <h3>Contact Us</h3>
            <p>Email: <a href="mailto:contact@skysurprise.com">contact@skysurprise.com</a></p>
            <p>Phone: <a href="tel:+46723456789">+46 723456789</a></p>
            <p>Address: <a href="#">Mysteriegatan 7, 111 45 Stockholm</a></p>
        </div>
        <div class="loggafooter">
            <img src="bilder/skysurpriselogo.png" alt="">
        </div>
    </div>
    <div class="botten">
        <p>&copy; 2025 SkySurprise. All rights reserved.</p>
    </div>
</div>

</body>
</html>
