<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function getDbConnection() {
    $servername = "localhost";
    $username = "root"; 
    $password = "";     
    $dbname = "skysurprise";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}


function getUserId() {
    return $_SESSION['user_id'] ?? null;
}


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

function isAdmin() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT userlevel FROM tbluser WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['userlevel'] == 9;
    }

    return false;
}
?>
