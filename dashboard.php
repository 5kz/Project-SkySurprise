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


if (isset($_POST['cancel_booking_id'])) {
    $cancelId = intval($_POST['cancel_booking_id']);
    $stmt = $conn->prepare("DELETE FROM bookinginfo WHERE id = ? AND userid = ?");
    $stmt->bind_param("ii", $cancelId, $userId);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}


if (isset($_POST['ticket_comment_submit'])) {
    $ticketId = intval($_POST['ticket_id']);
    $message = trim($_POST['message']);

    $stmt = $conn->prepare("SELECT status FROM ticketinfo WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $ticketId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $ticket = $result->fetch_assoc();
    $stmt->close();

    if ($ticket && $ticket['status'] === 'ongoing' && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO ticketcomments (ticket_id, user_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $ticketId, $userId, $message);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: dashboard.php");
    exit();
}


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


$daysLeftMessage = 'No upcoming flights. <br> <a href="boka-resa.php">Book one now!</a>';
$now = new DateTime();

$soonest = null;
foreach ($bookings as $booking) {
    $flightDate = new DateTime($booking['date']);
    if ($flightDate >= $now && (!$soonest || $flightDate < $soonest)) {
        $soonest = $flightDate;
    }
}

if ($soonest) {
    $interval = $now->diff($soonest);
    $daysLeft = $interval->format('%a');

    if ($daysLeft == 0) {
        $daysLeftMessage = "Your next flight is <strong>today</strong>!";
    } 
    else {
        $daysLeftMessage = "<strong>$daysLeft days</strong> left until your adventure!";
    }
}

$viewTicketId = isset($_GET['ticket']) ? intval($_GET['ticket']) : null;


$stmt = $conn->prepare("SELECT id, title, description, status, created_at, image FROM ticketinfo WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$ticketsResult = $stmt->get_result();

$tickets = [];
while ($row = $ticketsResult->fetch_assoc()) {
    $tickets[] = $row;
}
$stmt->close();


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
    <title>User Dashboard - SkySurprise</title>
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
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logga-ut.php">Log Out</a>
            <?php else: ?>
                <a href="logga-in.php">Log In</a>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="countdown-box">
        <h3><?= $daysLeftMessage ?></h3>
    </div>

    
    <div class="user-dashboard-container">

        
        <div class="user-bookings-section">
            <h2>My Bookings</h2>

            <?php if (count($bookings) > 0): ?>
                <table class="user-bookings-table">
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
        </div>

    
        <div class="user-tickets-section">
            <div class="user-support-create">
                <p>Need assistance?</p>
                <a href="support-kontakt.php">Create a ticket</a>
            </div>

            <h2>My Support Tickets</h2>

            <?php if (count($tickets) > 0): ?>
                <?php if ($viewTicketId): ?>
                    <?php
                    $selectedTicket = null;
                    foreach ($tickets as $t) {
                        if ($t['id'] == $viewTicketId) {
                            $selectedTicket = $t;
                            break;
                        }
                    }
                    ?>
                    <?php if ($selectedTicket): ?>
                        <div class="user-ticket-detail">
                            <h4>Ticket #<?= $selectedTicket['id'] ?> - <?= htmlspecialchars($selectedTicket['title']) ?></h4>
                            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($selectedTicket['description'])) ?></p>
                            <p><strong>Status:</strong> <?= htmlspecialchars($selectedTicket['status']) ?> | <strong>Created:</strong> <?= $selectedTicket['created_at'] ?></p>

                            <h5>Comments:</h5>
                            <?php if (isset($ticketComments[$selectedTicket['id']])): ?>
                                <ul class="user-ticket-comments">
                                    <?php foreach ($ticketComments[$selectedTicket['id']] as $comment): ?>
                                        <li><strong><?= htmlspecialchars($comment['surname'] . ' ' . $comment['lastname']) ?>:</strong> <?= nl2br(htmlspecialchars($comment['message'])) ?> <em>(<?= $comment['created_at'] ?>)</em></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>No comments yet.</p>
                            <?php endif; ?>

                            <?php if ($selectedTicket['status'] === 'ongoing'): ?>
                                <form method="post" class="user-ticket-reply-form">
                                    <textarea name="message" required placeholder="Reply to this ticket"></textarea>
                                    <input type="hidden" name="ticket_id" value="<?= $selectedTicket['id'] ?>">
                                    <button type="submit" name="ticket_comment_submit">Submit Reply</button>
                                </form>
                            <?php else: ?>
                                <p>You cannot comment on this ticket because it is not in 'Ongoing' status.</p>
                            <?php endif; ?>

                            <?php if (!empty($selectedTicket['image'])): ?>
                                <p><strong>Attached File:</strong><br>
                                    <a href="uploads/<?= htmlspecialchars($selectedTicket['image']) ?>" download>
                                        <?= htmlspecialchars($selectedTicket['image']) ?>
                                    </a>
                                </p>
                            <?php endif; ?>

                            <p><a href="dashboard.php" class="user-back-button">← Back to tickets</a></p>
                        </div>
                    <?php else: ?>
                        <p>Ticket not found or you do not have access.</p>
                        <p><a href="dashboard.php">← Back to tickets</a></p>
                    <?php endif; ?>
                <?php else: ?>
                    <?php foreach ($tickets as $ticket): ?>
                        <div class="user-ticket-summary">
                            <h4>Ticket #<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['title']) ?></h4>
                            <p><strong>Status:</strong> <?= htmlspecialchars($ticket['status']) ?> | <strong>Created:</strong> <?= $ticket['created_at'] ?></p>
                            <a href="dashboard.php?ticket=<?= $ticket['id'] ?>">View</a>
                        </div>
                        <hr>
                    <?php endforeach; ?>
                <?php endif; ?>
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

    <script>
        document.querySelectorAll('.user-ticket-summary a').forEach(link => {
            link.addEventListener('click', function () {
                localStorage.setItem('scrollPos', window.scrollY);
            });
        });

        window.addEventListener('load', function () {
            const scrollPos = localStorage.getItem('scrollPos');
            if (scrollPos) {
                window.scrollTo(0, parseInt(scrollPos));
                localStorage.removeItem('scrollPos');
            }
        });
    </script>
</body>
</html>
