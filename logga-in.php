<?php
require_once 'func.php'; // Se till att func filen finns

$error_message = ""; // Variabel för felmeddelande

// Kontrollera om formuläret har skickats via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn = getDbConnection(); // Tar fram databasanslutning från funktionen i func.php

    $email = htmlspecialchars($_POST['email']); // Säkerställer att email input är säker mot XSS
    $password = md5($_POST['password']); // Krypterar lösenordet med MD5 

    // Ta fram användarens data från databasen (email och lösenord) med sql fråga
    $stmt = $conn->prepare("SELECT * FROM tbluser WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password); 
    $stmt->execute(); 
    $result = $stmt->get_result(); // Hämtar resultatet från frågan

    // Om användaren finns i databasen
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc(); // Tar fram användarens data 
        $_SESSION['user_id'] = $user['id']; 
        $_SESSION['email'] = $user['email']; 
        header("Location: dashboard.php"); // Omdirigerar till dashboard
        exit(); // Avslutar om användningen är klar
    } else {
        $error_message = "Invalid email or password!"; // Sätter felmeddelande om inloggning misslyckas
    }

    $stmt->close(); 
    $conn->close(); // Stänger databasanslutning
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SkySurprise</title>
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
                <a href="main.php"><img src="bilder/skysurpriselogo.png" alt="Logo"></a> 
            </div>
        </div>
        <div class="headerright">
            <a href="main.php">Home</a>
            <a href="skapa-konto.php">Create Account</a>
        </div>
    </div>
    <div class="backgroundimglogin">
        <div class="login-container">
            <h2>Log In</h2>

            <!-- Visar felmeddelande om fel uppstår -->
            <?php if (!empty($error_message)): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <form action="logga-in.php" method="post" class="login-form">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Log In</button>
                <p>Don't have an account? <a href="skapa-konto.php">Create one</a></p>
            </form>
        </div>
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
                <p>Address: Mysteriegatan 7, 111 45 Stockholm</p>
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
