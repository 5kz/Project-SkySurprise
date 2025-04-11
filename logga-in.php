<?php
 require_once 'func.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $servername = "localhost";
    $username = "root"; 
    $password = "";     
    $dbname = "skysurprise";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    
    $email = htmlspecialchars($_POST['email']);
    $password = md5($_POST['password']); 

    
    $sql = "SELECT * FROM tbluser WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    
    if ($result->num_rows > 0) {
        
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id']; 
        $_SESSION['email'] = $user['email']; 
        
        
        header("Location: dashboard.php");
        exit(); 
    } else {
        $error_message = "Invalid email or password!";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SkySurprise</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
    <div class="header">
        <div class="headerleft">
            <a href="support-kontakt.php">Contact and support</a>
            <a href="om-oss.php">About us</a>
        </div>
        <div class="headermiddle">
            <div class="logo">
                <a href="main.php"><img src="bilder/skysurpriselogo.png" alt=""></a>
            </div>
        </div>
        <div class="headerright">
            <a href="main.php">Home</a>
            <a href="skapa-konto.php">Create Account</a>
        </div>
    </div>

    <div class="login-container">
        <h2>Log In</h2>
        <form action="logga-in.php" method="post" class="login-form">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Log In</button>
            <p>Don't have an account? <a href="skapa-konto.php">Create one</a></p>
        </form>
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
