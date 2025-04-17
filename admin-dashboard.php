<?php
require_once 'func.php';

// Check if the user is logged in and is an admin
if (!isAdmin()) {
    header("Location: dashboard.php"); // Redirect to user dashboard if not an admin
    exit();
}

$conn = getDbConnection();

// Handle deletion of a user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $userIdToDelete = intval($_POST['delete_user_id']);
    
    // Only delete if the user is not the admin itself
    if ($userIdToDelete !== getUserId()) {
        $deleteUser = $conn->prepare("DELETE FROM tbluser WHERE id = ?");
        $deleteUser->bind_param("i", $userIdToDelete);
        $deleteUser->execute();
        $deleteUser->close();
    }
}

// Fetch all users from the database
$usersQuery = "SELECT id, surname, lastname, email, userlevel FROM tbluser";
$users = $conn->query($usersQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SkySurprise</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
    <div class="header">
        <div class="headerleft">
            <a href="dashboard.php">My journey</a>
            <a href="om-oss.php">About Us</a>
        </div>
        <div class="headermiddle">
            <div class="logo">
                <a href="main.php"><img src="bilder/skysurpriselogo.png" alt="SkySurprise Logo"></a>
            </div>
        </div>
        <div class="headerright">
            <a href="main.php">Home</a>
            <a href="logga-ut.php">Log Out</a>
        </div>
    </div>

    <div class="content">
        <h2>Manage Users</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>User Level</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['surname']) . ' ' . htmlspecialchars($user['lastname']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="delete_user_id" value="<?= $user['id'] ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h2>Manage Bookings</h2>
        <!-- Admin can manage flight bookings here (if needed) -->
        <!-- You can add similar logic to manage, unbook, or delete flight bookings -->
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
                <img src="bilder/skysurpriselogo.png" alt="SkySurprise Logo">
            </div>
        </div>
        <div class="botten">
            <p>&copy; 2025 SkySurprise. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
