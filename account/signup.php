<?php
session_start();

// Show all PHP errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$database = "recipe_social";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data and trim spaces
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate input
    if (empty($username) || empty($email) || empty($password)) {
        $message = "All fields are required.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Email already registered";
        } else {
            // Hash the password
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $bio = NULL;
            $profile_image = NULL;

            // Insert new user
            $stmt_insert = $conn->prepare(
                "INSERT INTO users (username, email, password, bio, profile_image) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt_insert->bind_param("sssss", $username, $email, $hashed_pass, $bio, $profile_image);

            if ($stmt_insert->execute()) {
                // Success: redirect to login page
                header("Location: account.php?signup=success");
                exit();
            } else {
                $message = "Database error: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FoodieHub | Sign Up</title>
<link rel="stylesheet" type="text/css" href="../account/style.css">
<style>
.error { color: red; }
.success { color: green; }
</style>
</head>
<body>

<div id="navbar">
    <div id="brand">
        <img id="logo" src="../images/logo.png" alt="logo">
        <h1 id="title">FoodieHub</h1>
    </div>
    <ul>
        <li><a href="/FoodieHub/index.php" title="Home"><img src="../images/home.png" alt="home"></a></li>
        <li><a href="/FoodieHub/recipes.php" title="Recipes"><img src="../images/recipes.png" alt="recipes"></a></li>
        <li><a href="/FoodieHub/about/about.php" title="Info"><img src="../images/info.png" alt="info"></a></li>
        <li><a href="/FoodieHub/account/account.php"><img src="../images/account.png" alt="account"></a></li>
    </ul>
</div>

<header>
    <h1>Sign Up</h1>
</header>

<div class="login">
    <form method="POST" action="signup.php">
        <h3 id="login">SIGN UP</h3>

        <label for="username">USERNAME</label><br>
        <input type="text" name="username" placeholder="John Doe" required><br>

        <label for="email">EMAIL</label><br>
        <input type="email" name="email" placeholder="your@email.com" required><br>

        <label for="password">PASSWORD</label><br>
        <input type="password" name="password" placeholder="*******" required><br>

        <button type="submit" id="btnlogin">Create Account</button>

        <p>Have an account? <a href="account.php">Login</a></p>

        <?php
        if(!empty($message)){
            echo "<p class='error'>$message</p>";
        }
        ?>
    </form>
</div>

</body>
</html>
