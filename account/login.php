<?php
session_start();


// Show errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$database = "recipe_social";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Fetch user by email
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $username, $hashed_pass);
        $stmt->fetch();

        // Verify password
        if (password_verify($password, $hashed_pass)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;


            header("Location: ../index.php");
    
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            exit();
        } else {
            // Password incorrect
            header("Location: account.php?error=1");
            exit();
        }
    } else {
        // Email not found
        header("Location: account.php?error=1");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>
