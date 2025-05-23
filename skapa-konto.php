<?php

require_once 'func.php'; //se till att func filen finns

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Hämta datan från formuläret
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname = htmlspecialchars($_POST['lastname']);
    $email = htmlspecialchars($_POST['email']);
    $password = md5($_POST['password']); 

    // Skapa en databasanslutning med func.php filen och skapa SQL-fråga
    $conn = getDbConnection();  
    $sql = "INSERT INTO tbluser (surname, lastname, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $firstname, $lastname, $email, $password);

    if ($stmt->execute()) {
        // Skicka till login sidan om registering funkar 
        header("Location: logga-in.php");
        exit();
    } else {
        // Vid fel, visa felmeddelande
        $error_message = "Error: " . $stmt->error;
    }

    // Stäng anslutningen
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - SkySurprise</title>
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
                <a href="main.php"><img src="bilder/skysurpriselogo.png" alt=""></a>
            </div>
        </div>
        <div class="headerright">
            <a href="main.php">Home</a>
            <a href="logga-in.php">Log in</a>
        </div>
    </div>
    <div class="registerbackgroundimg">
        <div class="register-container">
            <h2>Create an Account</h2>

            <?php if (!empty($error_message)): ?>
                <div style="color:red; margin-bottom:10px;">
                    <?= $error_message ?>
                </div>
            <?php endif; ?>

            <form action="skapa-konto.php" method="post" class="register-form">
                <label for="firstname">First Name:</label>
                <input type="text" id="firstname" name="firstname" required>

                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" name="lastname" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Register</button>
            </form>
        </div>
    </div>
    <div class="footer">
        <div class="footerinfo">
            <div class="kortinfo">
                <h2>SkySurprise</h2>
                <p>Pack your bags. We'll handle rest<br> 
            </div>
            <div class="foretagsinfo">
                <h3>Contact Us</h3>
                <p>Email: <a href="mailto:contact@skysurprise.com">contact@skysurprise.com</a></p>
                <p>Phone: <a href="tel:+46723456789">+46 723456789</a></p>
                <p>Address: <a href="">Mysteriegatan 7, 111 45 Stockholm</a></p>
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
