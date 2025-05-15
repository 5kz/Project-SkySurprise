<?php

require_once 'func.php'; // Se tilll att func.php finns


if (!getUserId()) {
    header("Location: logga-in.php"); // Om användaren inte är inloggad skicka till logga-in sidan
    exit();
}


$success = false; // Skapa variabel
$error = false;


if ($_SERVER["REQUEST_METHOD"] === "POST") { // Kolla om formuläret har skickats via POST
    
    $conn = getDbConnection(); // Använd func filens funktion för att skapa databasanslutning

    //Information från formuläret
    $userId = getUserId(); // Hämta användare id med hjälp av funktionen i func.php
    $title = trim($_POST["title"]); 
    $description = trim($_POST["description"]);
    $image = null;

    // Hanreting av bilduppladdning
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === UPLOAD_ERR_OK) {
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif']; // TIllåta dessa filtyper
        $fileType = $_FILES["image"]["type"];
        $fileTmpName = $_FILES["image"]["tmp_name"];
        $fileName = basename($_FILES["image"]["name"]);

        // Kolla om filen är av tillåten typ
        if (in_array($fileType, $allowedTypes)) {
            // Skapa upplysningsmapp om den inte finns samt ge alla bilder ett unikt namn
            $uploadDir = "uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $targetPath = $uploadDir . time() . "_" . $fileName;
            
            if (move_uploaded_file($fileTmpName, $targetPath)) { // Flytta filen till den nya rätta mappen
                $image = $targetPath; // När sökvägen är fixad kan den användas till senare
            }
        }
    }

    
    if (!empty($title) && !empty($description)) { // Kolla så att titeln och beskrivningen inte är tomma
        // Förbereda SQL-fråga för att sätta in data i databasen
        $stmt = $conn->prepare("INSERT INTO ticketinfo (user_id, title, description, image, status) VALUES (?, ?, ?, ?, 'open')");
        $stmt->bind_param("isss", $userId, $title, $description, $image);

       // Om det funkar så sätt success till true annars sätt error till true
        if ($stmt->execute()) {
            $success = true; 
        } else {
            $error = true; 
        }

        
        $stmt->close();
        $conn->close();
    } else {
        // Om titeln eller beskrivningen är tom, sätt error till true
        $error = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Support Ticket - SkySurprise</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>">
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
            <a href="logga-ut.php">Log Out</a> <!-- Visa logga in / logg ut beronende på om användern är inloggad eller ej -->
        <?php else: ?>
            <a href="logga-in.php">Log In</a>
        <?php endif; ?>
    </div>
</div>


<div class="kontakt-formcontainer">
    <h2>Submit a Support Ticket</h2>
    <?php if ($success): ?> <!-- Om det funkar -->
        <p class="success">Your ticket has been submitted successfully.</p>
    <?php elseif ($error): ?> <!-- Om det inte funkar -->
        <p class="error">There was a problem submitting your ticket. Make sure all fields are filled in.</p>
    <?php endif; ?>


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
