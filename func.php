<?php
// Start session (only if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection function
function getDbConnection() {
    $servername = "localhost";
    $username = "root"; // Update this if needed
    $password = "";     // Update this if needed
    $dbname = "skysurprise";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

// Get current logged-in user ID
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get full name of the current user
function getFullName() {
    $userId = getUserId();

    if (!$userId) return null;

    $conn = getDbConnection();
    
    $stmt = $conn->prepare("SELECT surname, lastname FROM tbluser WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($surname, $lastname);

    if ($stmt->fetch()) {
        $stmt->close();
        $conn->close();
        return $surname . ' ' . $lastname;
    } else {
        $stmt->close();
        $conn->close();
        return null;
    }
}
?>
