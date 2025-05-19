<?php
// ser till att func filen finns
require_once 'func.php';

// Kontrollera om användaren är administratör, om inte skicka till dashboard
if (!isAdmin()) {
    header("Location: dashboard.php");
    exit();
}

// databasanslutning
$conn = getDbConnection();


// funktion för att ta bort en post från en tabell
function deleteById($conn, $table, $id) {
    $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Tar bort användare om 'delete_user' har skickats med POST 
if (isset($_POST['delete_user'])) {
    deleteById($conn, 'tbluser', intval($_POST['delete_user']));
}

// Tar bort en bokning om 'cancel_booking_id' har skickats med POST
if (isset($_POST['cancel_booking_id'])) {
    deleteById($conn, 'bookinginfo', intval($_POST['cancel_booking_id']));
}

// Uppdaterar statusen för en ticket (öppen, pågående, stängd)
if (isset($_POST['update_ticket_status'])) {
    $ticketId = intval($_POST['ticket_id']);
    $newStatus = $_POST['status'];

    $stmt = $conn->prepare("UPDATE ticketinfo SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $ticketId);
    $stmt->execute();
    $stmt->close();

    // Ladda om sidan med rätt filter
    header("Location: admin-dashboard.php?filter=" . urlencode($_POST['filter']));
    exit();
}

// för att lägga till en kommentar på en supportticket
if (isset($_POST['ticket_comment_submit'])) {
    $ticketId = intval($_POST['ticket_id']);
    $message = trim($_POST['message']);
    $userId = getUserId(); // Hämtar ID för den inloggade användaren

    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO ticketcomments (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $ticketId, $userId, $message);
        $stmt->execute();
        $stmt->close();
    }

    // Omdirigera tillbaka med rätt filter
    header("Location: admin-dashboard.php?filter=" . urlencode($_POST['filter']));
    exit();
}

// Hämtar alla användare för att visa i adminpanelen
$users = $conn->query("SELECT id, surname, lastname, email FROM tbluser");

// Hämtar alla bokningar och kopplar dem till användare
$bookings = $conn->query("
    SELECT b.id AS booking_id, b.departure, b.date, b.destinationtype, u.surname, u.lastname 
    FROM bookinginfo b 
    JOIN tbluser u ON b.userid = u.id 
    ORDER BY b.date DESC
");

// Hämtar filterstatus från URL eller sätter open (standardvärde)
$filter = $_GET['filter'] ?? 'open';
$viewTicketId = isset($_GET['ticket']) ? intval($_GET['ticket']) : null;
$tickets = [];

// Förbereder SQL beroende på om vi visar alla eller vill filtrera tickets
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

// Utför frågan och samlar resultatet
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $tickets[] = $row;
}
$stmt->close();

// Hämtar alla kommentarer och grupperar dem per ticket_id
$ticketComments = [];
$commentsResult = $conn->query("
    SELECT c.*, u.surname, u.lastname 
    FROM ticketcomments c 
    JOIN tbluser u ON c.user_id = u.id 
    ORDER BY c.created_at ASC
");

while ($row = $commentsResult->fetch_assoc()) {
    $ticketComments[$row['ticket_id']][] = $row;
}

// Stänger databaskopplingen
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SkySurprise</title>
    
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
                <a href="main.php"><img src="bilder/skysurpriselogo.png" alt="SkySurprise Logo"></a>
            </div>
        </div>
        <div class="headerright">
            <a href="main.php">Home</a>
            <!-- Visar Logga ut om användare är inloggad, annars Logga in -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logga-ut.php">Log Out</a>
            <?php else: ?>
                <a href="logga-in.php">Log In</a>
            <?php endif; ?>
        </div>
    </div>


    <div class="admin-dashboard">
        <div class="admin-content">
            <h2>Admin Dashboard</h2>

          
            <div class="admin-tickets">
                <h3>Support Tickets</h3>

                <!-- Filter för ticket-status -->
                <form method="get" class="admin-form">
                    <label for="filter">Filter by status:</label>
                    <select name="filter" onchange="this.form.submit()">
                        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
                        <option value="open" <?= $filter === 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="ongoing" <?= $filter === 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                        <option value="closed" <?= $filter === 'closed' ? 'selected' : '' ?>>Closed</option>
                    </select>
                </form>

                <!-- Loopar igenom alla tickets -->
                <?php foreach ($tickets as $ticket): ?>
                    <?php $isExpanded = ($viewTicketId === intval($ticket['id'])); ?>
                    <div class="admin-ticket">
                        <h4>Ticket #<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['title']) ?></h4>
                        <p><strong>Status:</strong> <?= htmlspecialchars($ticket['status']) ?> |
                        <strong>Created:</strong> <?= $ticket['created_at'] ?></p>
                        <p><strong>User:</strong> <?= htmlspecialchars($ticket['surname'] . ' ' . $ticket['lastname']) ?> (ID: <?= $ticket['user_id'] ?>)</p>

                        <!-- Visa "View"-länk eller andra detaljer beroende på om ticket är vald -->
                        <?php if (!$isExpanded): ?>
                            <a href="admin-dashboard.php?filter=<?= urlencode($filter) ?>&ticket=<?= $ticket['id'] ?>">View</a>
                        <?php else: ?>
                            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
                            <p><strong>Attached File:</strong><br>
                                <a href="uploads/<?= htmlspecialchars($ticket['image']) ?>" download>
                                    <?= htmlspecialchars($ticket['image']) ?>
                                </a>
                            </p>

                            <!-- Formulär för att uppdatera ticket-status -->
                            <form method="post" class="admin-form">
                                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                                <select name="status">
                                    <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                                    <option value="ongoing" <?= $ticket['status'] === 'ongoing' ? 'selected' : '' ?>>Ongoing</option>
                                    <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                                </select>
                                <button type="submit" name="update_ticket_status">Update Status</button>
                            </form>

                            <!-- Kommentarer till ticket med sanering -->
                            <h5>Comments:</h5>
                            <?php if (isset($ticketComments[$ticket['id']])): ?>
                                <ul class="admin-comment-list">
                                    <?php foreach ($ticketComments[$ticket['id']] as $comment): ?>
                                        <li>
                                            <strong><?= htmlspecialchars($comment['surname'] . ' ' . $comment['lastname']) ?>:</strong>
                                            <?= nl2br(htmlspecialchars($comment['message'])) ?>
                                            <em>(<?= $comment['created_at'] ?>)</em>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>No comments yet.</p>
                            <?php endif; ?>

                            <!-- Formulär för att lägga till ny kommentar med sanering -->
                            <form method="post" class="admin-form">
                                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                                <textarea name="message" required placeholder="Reply to this ticket"></textarea>
                                <button type="submit" name="ticket_comment_submit">Submit Reply</button>
                            </form>

                            <p><a href="admin-dashboard.php?filter=<?= urlencode($filter) ?>" class="admin-back-button">← Back to tickets</a></p>
                        <?php endif; ?>
                    </div>
                    <hr>
                <?php endforeach; ?>
            </div>

            <!-- Bokningar -->
            <div class="admin-bookings">
                <h3>All Bookings</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th><th>From</th><th>To</th><th>Date</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = $bookings->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($booking['surname'] . ' ' . $booking['lastname']) ?></td>
                                <td><?= htmlspecialchars($booking['departure']) ?></td>
                                <td><?= htmlspecialchars($booking['destinationtype']) ?></td>
                                <td><?= htmlspecialchars($booking['date']) ?></td>
                                <td>
                                    <!-- Formulär för att avboka -->
                                    <form method="post" class="admin-form">
                                        <input type="hidden" name="cancel_booking_id" value="<?= $booking['booking_id'] ?>">
                                        <button type="submit">Cancel</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Användarhantering -->
            <div class="admin-users">
                <h3>All Users</h3>
                <table class="admin-table">
                    <thead>
                        <tr><th>Name</th><th>Email</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['surname'] . ' ' . $user['lastname']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <!-- Formulär för att radera användare -->
                                    <form method="post" class="admin-form">
                                        <input type="hidden" name="delete_user" value="<?= $user['id'] ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
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

    <!-- Script för att komma ihåg scrollposition -->
    <script>
    document.querySelectorAll('.admin-ticket a[href*="ticket="]').forEach(link => {
        link.addEventListener('click', function () {
            localStorage.setItem('adminScrollPos', window.scrollY);
        });
    });

    window.addEventListener('load', function () {
        const scrollPos = localStorage.getItem('adminScrollPos');
        if (scrollPos !== null) {
            window.scrollTo(0, parseInt(scrollPos));
            localStorage.removeItem('adminScrollPos');
        }
    });
    </script>
</body>
</html>
