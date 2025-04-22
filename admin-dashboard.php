<?php
require_once 'func.php';

if (!isAdmin()) {
    header("Location: dashboard.php");
    exit();
}

$conn = getDbConnection();

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $userIdToDelete = intval($_POST['delete_user']);
    $stmt = $conn->prepare("DELETE FROM tbluser WHERE id = ?");
    $stmt->bind_param("i", $userIdToDelete);
    $stmt->execute();
    $stmt->close();
}

// Handle booking cancellation
if (isset($_POST['cancel_booking_id'])) {
    $bookingId = intval($_POST['cancel_booking_id']);
    $stmt = $conn->prepare("DELETE FROM bookinginfo WHERE id = ?");
    $stmt->bind_param("i", $bookingId);
    $stmt->execute();
    $stmt->close();
}

// Handle ticket status update
if (isset($_POST['update_ticket_status'])) {
    $ticketId = intval($_POST['ticket_id']);
    $newStatus = $_POST['status'];
    $stmt = $conn->prepare("UPDATE ticketinfo SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $ticketId);
    $stmt->execute();
    $stmt->close();
    header("Location: admin-dashboard.php?filter=" . urlencode($_POST['filter']));
    exit();
}

// Handle ticket comment
if (isset($_POST['ticket_comment_submit'])) {
    $ticketId = intval($_POST['ticket_id']);
    $message = trim($_POST['message']);
    $userId = getUserId();

    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO ticketcomments (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $ticketId, $userId, $message);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin-dashboard.php?filter=" . urlencode($_POST['filter']));
    exit();
}

// Fetch all users
$users = $conn->query("SELECT id, surname, lastname, email FROM tbluser");


$viewTicketId = isset($_GET['ticket']) ? intval($_GET['ticket']) : null;

// Fetch all bookings
$bookingsQuery = "
    SELECT b.id AS booking_id, b.departure, b.date, b.destinationtype, u.surname, u.lastname 
    FROM bookinginfo b 
    JOIN tbluser u ON b.userid = u.id 
    ORDER BY b.date DESC
";
$bookings = $conn->query($bookingsQuery);

// Handle ticket filter
$filter = $_GET['filter'] ?? 'open';
$tickets = [];

if ($filter === 'all') {
    $stmt = $conn->prepare("
        SELECT t.*, u.surname, u.lastname 
        FROM ticketinfo t 
        JOIN tbluser u ON t.user_id = u.id 
        ORDER BY t.created_at DESC
    ");
} else {
    $stmt = $conn->prepare("
        SELECT t.*, u.surname, u.lastname 
        FROM ticketinfo t 
        JOIN tbluser u ON t.user_id = u.id 
        WHERE t.status = ?
        ORDER BY t.created_at DESC
    ");
    $stmt->bind_param("s", $filter);
}

$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tickets[] = $row;
}
$stmt->close();

// Fetch ticket comments
$commentsQuery = "
    SELECT c.*, u.surname, u.lastname 
    FROM ticketcomments c 
    JOIN tbluser u ON c.user_id = u.id
    ORDER BY c.created_at ASC
";
$commentsResult = $conn->query($commentsQuery);
$ticketComments = [];

while ($row = $commentsResult->fetch_assoc()) {
    $ticketComments[$row['ticket_id']][] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SkySurprise</title>
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
        <h2>Admin Dashboard</h2>

        <h3>All Users</h3>
        <table class="booking-table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['surname'] . ' ' . $user['lastname']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="delete_user" value="<?= $user['id'] ?>">
                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h3>All Bookings</h3>
        <table class="booking-table">
            <thead>
                <tr><th>Name</th><th>From</th><th>To</th><th>Date</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php while ($booking = $bookings->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($booking['surname'] . ' ' . $booking['lastname']) ?></td>
                        <td><?= htmlspecialchars($booking['departure']) ?></td>
                        <td><?= htmlspecialchars($booking['destinationtype']) ?></td>
                        <td><?= htmlspecialchars($booking['date']) ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="cancel_booking_id" value="<?= $booking['booking_id'] ?>">
                                <button type="submit">Cancel</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h3>Support Tickets</h3>
        <form method="get">
            <label for="filter">Filter by status:</label>
            <select name="filter" onchange="this.form.submit()">
                <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
                <option value="open" <?= $filter === 'open' ? 'selected' : '' ?>>Open</option>
                <option value="ongoing" <?= $filter === 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                <option value="closed" <?= $filter === 'closed' ? 'selected' : '' ?>>Closed</option>
            </select>
        </form>
        <?php foreach ($tickets as $ticket): ?>
    <?php $isExpanded = ($viewTicketId === intval($ticket['id'])); ?>
    <div class="ticket">
        <h4>Ticket #<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['title']) ?></h4>
        <p><strong>Status:</strong> <?= htmlspecialchars($ticket['status']) ?> | <strong>Created:</strong> <?= $ticket['created_at'] ?></p>
        <p><strong>User:</strong> <?= htmlspecialchars($ticket['surname'] . ' ' . $ticket['lastname']) ?> (ID: <?= $ticket['user_id'] ?>)</p>

        <?php if (!$isExpanded): ?>
            <a href="admin-dashboard.php?filter=<?= urlencode($filter) ?>&ticket=<?= $ticket['id'] ?>">View</a>
        <?php else: ?>
            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
            <p><strong>Attached File:</strong><br>
        <a href="uploads/<?= htmlspecialchars($ticket['image']) ?>" download>
            <?= htmlspecialchars($ticket['image']) ?>
        </a>
    </p>

            <form method="post">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                <select name="status">
                    <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                    <option value="ongoing" <?= $ticket['status'] === 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                    <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                </select>
                <button type="submit" name="update_ticket_status">Update Status</button>
            </form>

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

            <form method="post">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                <textarea name="message" required placeholder="Reply to this ticket"></textarea>
                <button type="submit" name="ticket_comment_submit">Submit Reply</button>
            </form>

            <!-- Back Button -->
            <p><a href="admin-dashboard.php?filter=<?= urlencode($filter) ?>" class="back-button">← Back to tickets</a></p>
        <?php endif; ?>
    </div>
    <hr>
<?php endforeach; ?>
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
