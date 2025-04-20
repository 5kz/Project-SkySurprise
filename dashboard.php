<?php
require_once 'func.php';

if (isAdmin()) {
    header("Location: admin-dashboard.php");
    exit();
}
if (!getUserId()) {
    header("Location: logga-in.php");
    exit();
}

$conn = getDbConnection();
$userId = getUserId();

// Handle ticket comment submission
if (isset($_POST['ticket_comment_submit'])) {
    $ticketId = intval($_POST['ticket_id']);
    $message = trim($_POST['message']);

    // Check if the ticket status is "ongoing" before allowing the user to comment
    $stmt = $conn->prepare("SELECT status FROM ticketinfo WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $ticketId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $ticket = $result->fetch_assoc();
    $stmt->close();

    if ($ticket && $ticket['status'] === 'ongoing' && !empty($message)) {
        // Insert the comment into the database if status is "ongoing"
        $stmt = $conn->prepare("INSERT INTO ticketcomments (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $ticketId, $userId, $message);
        $stmt->execute();
        $stmt->close();
    }

    // After submitting the comment, redirect to prevent resubmission
    header("Location: dashboard.php");
    exit();
}

// Fetch user bookings
$stmt = $conn->prepare("SELECT b.id AS booking_id, b.departure, b.date, b.destinationtype, u.surname, u.lastname 
                        FROM bookinginfo b 
                        JOIN tbluser u ON b.userid = u.id 
                        WHERE b.userid = ? 
                        ORDER BY b.date DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$bookingsResult = $stmt->get_result();

$bookings = [];
while ($row = $bookingsResult->fetch_assoc()) {
    $bookings[] = $row;
}
$stmt->close();

// Fetch user's support tickets
$stmt = $conn->prepare("SELECT id, title, description, status, created_at FROM ticketinfo WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$ticketsResult = $stmt->get_result();

$tickets = [];
while ($row = $ticketsResult->fetch_assoc()) {
    $tickets[] = $row;
}
$stmt->close();

// Fetch comments for each ticket
$ticketComments = [];
foreach ($tickets as $ticket) {
    $ticketId = $ticket['id'];
    $stmt = $conn->prepare("SELECT c.*, u.surname, u.lastname FROM ticketcomments c JOIN tbluser u ON c.user_id = u.id WHERE c.ticket_id = ? ORDER BY c.created_at ASC");
    $stmt->bind_param("i", $ticketId);
    $stmt->execute();
    $commentsResult = $stmt->get_result();

    while ($row = $commentsResult->fetch_assoc()) {
        $ticketComments[$ticketId][] = $row;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - SkySurprise</title>
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
                        <th>Departure from</th>
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
                                    <input type="hidden" name="cancel_booking_id" value="<?= $booking['booking_id'] ?>">
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

        <div class="support">
            <div class="helptitle">
                <div class="help">
                    <p>Need assistance?</p>
                    <a href="support-kontakt.php">Create a ticket</a>
                </div>
            </div>

            <h2>My Support Tickets</h2>
            <?php if (count($tickets) > 0): ?>
                <?php foreach ($tickets as $ticket): ?>
                    <div class="ticket">
                        <h4>Ticket #<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['title']) ?></h4>
                        <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
                        <p><strong>Status:</strong> <?= htmlspecialchars($ticket['status']) ?> | <strong>Created:</strong> <?= $ticket['created_at'] ?></p>

                        <h5>Comments:</h5>
                        <?php if (isset($ticketComments[$ticket['id']])): ?>
                            <ul>
                                <?php foreach ($ticketComments[$ticket['id']] as $comment): ?>
                                    <li><strong><?= htmlspecialchars($comment['surname'] . ' ' . $comment['lastname']) ?>:</strong> <?= nl2br(htmlspecialchars($comment['message'])) ?> <em>(<?= $comment['created_at'] ?>)</em></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No comments yet.</p>
                        <?php endif; ?>

                        <!-- Only allow commenting if the ticket status is "ongoing" -->
                        <?php if ($ticket['status'] === 'ongoing'): ?>
                            <form method="post">
                                <textarea name="message" required placeholder="Reply to this ticket"></textarea>
                                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                                <button type="submit" name="ticket_comment_submit">Submit Reply</button>
                            </form>
                        <?php else: ?>
                            <p>You cannot comment on this ticket because it is not in 'Ongoing' status.</p>
                        <?php endif; ?>
                    </div>
                    <hr>
                <?php endforeach; ?>
            <?php else: ?>
                <p>You have not submitted any support tickets yet.</p>
            <?php endif; ?>
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
