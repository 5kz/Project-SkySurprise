<?php
require_once 'func.php';

// Redirect if not logged in
if (!getUserId()) {
    header("Location: logga-in.php");
    exit();
}

$conn = getDbConnection();
$userId = getUserId();

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking_id'])) {
    $bookingId = intval($_POST['cancel_booking_id']);

    // Delete only if it belongs to the current user
    $check = $conn->prepare("SELECT id FROM bookinginfo WHERE id = ? AND userid = ?");
    $check->bind_param("ii", $bookingId, $userId);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $delete = $conn->prepare("DELETE FROM bookinginfo WHERE id = ?");
        $delete->bind_param("i", $bookingId);
        $delete->execute();
        $delete->close();
    }

    $check->close();
}

// Fetch user's bookings
$stmt = $conn->prepare("SELECT id, departure, date, destinationtype FROM bookinginfo WHERE userid = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - SkySurprise</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
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

    <div class="content">
        <h2>My Bookings</h2>

        <?php if (count($bookings) > 0): ?>
            <table class="booking-table">
                <thead>
                    <tr>
                        <th>Departure</th>
                        <th>Date</th>
                        <th>Destination</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?= htmlspecialchars($booking['departure']) ?></td>
                            <td><?= htmlspecialchars($booking['date']) ?></td>
                            <td><?= htmlspecialchars($booking['destinationtype']) ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="cancel_booking_id" value="<?= $booking['id'] ?>">
                                    <button type="submit">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have no booked flights yet.</p>
        <?php endif; ?>
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
