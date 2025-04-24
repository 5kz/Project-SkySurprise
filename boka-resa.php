<?php
require_once 'func.php';

if (!getUserId()) {
    header("Location: logga-in.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $departure = filter_input(INPUT_POST, 'departure', FILTER_SANITIZE_STRING);
    $destinationtype = filter_input(INPUT_POST, 'destinationtype', FILTER_SANITIZE_STRING);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $userid = getUserId();

    if (!empty($departure) && !empty($destinationtype) && !empty($date)) {
        $conn = getDbConnection();
        $stmt = $conn->prepare("INSERT INTO bookinginfo (departure, date, destinationtype, userid) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $departure, $date, $destinationtype, $userid);

        if ($stmt->execute()) {
            $success = "Your flight has been successfully booked!";
        } else {
            $error = "An error occurred while booking: " . $conn->error;
        }

        $stmt->close();
        $conn->close();
    } else {
        $error = "All fields must be filled out.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Flight - SkySurprise</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body class="booking-page">

    <!-- Header Section (unchanged) -->
    <div class="header">
        <div class="headerleft">
            <a href="dashboard.php">My journey</a>
            <a href="om-oss.php">About us</a>
        </div>
        <div class="headermiddle">
            <div class="logo">
                <a href="main.php"><img src="bilder/skysurpriselogo.png" alt="SkySurprise Logo"></a>
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

    <!-- Content Section -->
    <div class="booking-content">
        <h2 class="booking-heading">Book Your Flight</h2>

        <!-- Success / Error Messages -->
        <?php if ($success): ?>
            <p class="booking-message booking-message-success"><?= htmlspecialchars($success) ?></p>
        <?php elseif ($error): ?>
            <p class="booking-message booking-message-error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <!-- Booking Form -->
        <form method="post" action="boka-resa.php" class="booking-form">
            <div class="booking-form-group">
                <label for="departure">Departure:</label>
                <select name="departure" id="departure" required>
                    <option value="">-- Select Departure location --</option>
                    <optgroup label="Sweden">
                        <option value="Stockholm Arlanda (ARN)">Stockholm Arlanda (ARN)</option>
                        <option value="Göteborg Landvetter (GOT)">Göteborg Landvetter (GOT)</option>
                        <option value="Malmö Sturup (MMX)">Malmö Sturup (MMX)</option>
                    </optgroup>
                    <optgroup label="Denmark">
                        <option value="Copenhagen Kastrup (CPH)">Copenhagen Kastrup (CPH)</option>
                    </optgroup>
                    <optgroup label="Norway">
                        <option value="Oslo Gardermoen (OSL)">Oslo Gardermoen (OSL)</option>
                    </optgroup>
                    <optgroup label="Finland">
                        <option value="Helsinki-Vantaa (HEL)">Helsinki-Vantaa (HEL)</option>
                    </optgroup>
                </select>
            </div>

            <div class="booking-form-group">
                <label for="date">Date of Flight:</label>
                <input type="date" name="date" id="date" required>
            </div>

            <div class="booking-form-group">
                <label for="destinationtype">Destination Type:</label>
                <select name="destinationtype" id="destinationtype" required>
                    <option value="">-- Select Destination Type --</option>
                    <option value="City escape">City escape</option>
                    <option value="Beach and relax">Beach and relax</option>
                    <option value="Nature & adventure">Nature & adventure</option>
                    <option value="Cultural & historic">Cultural & historic</option>
                    <option value="Surprise me">Surprise me</option>
                </select>
            </div>

            <button type="submit" class="booking-submit-button">Book Flight</button>
        </form>
    </div>

    <!-- Footer Section (unchanged) -->
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
                <img src="bilder/skysurpriselogo.png" alt="SkySurprise Logo">
            </div>
        </div>
        <div class="botten">
            <p>&copy; 2025 SkySurprise. All rights reserved.</p>
        </div>
    </div>

</body>
</html>
