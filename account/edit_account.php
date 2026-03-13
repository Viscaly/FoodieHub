<?php
session_start();
include '../database/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: account.php");
    exit();
}

$message = "";
$message_type = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    $current_password = trim($_POST['current_password']);

    if(empty($username) || empty($email)){
        $message = "Username and Email cannot be empty.";
        $message_type = "error";
    } else {
        // Verify current password first
        $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->bind_result($hashed);
        $stmt->fetch();
        $stmt->close();

        if(!password_verify($current_password, $hashed)){
            $message = "Current password is incorrect.";
            $message_type = "error";
        } elseif(!empty($new_password) && $new_password !== $confirm_password){
            $message = "New passwords do not match.";
            $message_type = "error";
        } elseif(!empty($new_password) && strlen($new_password) < 6){
            $message = "New password must be at least 6 characters.";
            $message_type = "error";
        } else {
            if(!empty($new_password)){
                $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username=?, email=?, password=? WHERE id=?");
                $stmt->bind_param("sssi", $username, $email, $new_hashed, $_SESSION['user_id']);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE id=?");
                $stmt->bind_param("ssi", $username, $email, $_SESSION['user_id']);
            }

            if($stmt->execute()){
                $_SESSION['username'] = $username;
                $message = "Account updated successfully!";
                $message_type = "success";
            } else {
                $message = "Error: " . $stmt->error;
                $message_type = "error";
            }
            $stmt->close();
        }
    }
}

$stmt = $conn->prepare("SELECT username, email FROM users WHERE id=?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($current_username, $current_email);
$stmt->fetch();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FoodieHub | Edit Account</title>
<link rel="stylesheet" type="text/css" href="../style.css">
<style>
.edit-container {
  max-width: 440px;
  margin: 50px auto;
  padding: 32px;
  background: rgb(40, 35, 35);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 12px;
  color: #f5f5f5;
}
.edit-container h2 {
  margin-top: 0;
  margin-bottom: 6px;
  font-size: 22px;
}
.section-label {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  color: #ff7a18;
  margin: 24px 0 10px;
  padding-bottom: 6px;
  border-bottom: 1px solid rgba(255,255,255,0.08);
}
.edit-container label {
  display: block;
  margin-top: 14px;
  font-size: 13px;
  font-weight: 600;
  color: #aaa;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.edit-container input[type=text],
.edit-container input[type=email],
.edit-container input[type=password] {
  width: 100%;
  padding: 10px 12px;
  margin-top: 6px;
  border-radius: 7px;
  border: 1px solid rgba(255,255,255,0.15);
  background: rgba(255,255,255,0.07);
  color: #fff;
  font-size: 14px;
  box-sizing: border-box;
  transition: border-color 0.2s;
}
.edit-container input:focus {
  outline: none;
  border-color: #ff7a18;
}
.edit-container input::placeholder {
  color: rgba(255,255,255,0.25);
}
.edit-container button {
  margin-top: 24px;
  width: 100%;
  padding: 12px;
  background: #ff7a18;
  color: white;
  border: none;
  border-radius: 7px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.edit-container button:hover { background: #e06615; }
.hint {
  font-size: 12px;
  color: rgba(255,255,255,0.3);
  margin-top: 4px;
}
p.message {
  margin-top: 14px;
  padding: 10px 14px;
  border-radius: 7px;
  font-size: 14px;
}
p.success {
  background: rgba(92, 184, 92, 0.15);
  color: #5cb85c;
  border: 1px solid rgba(92,184,92,0.3);
}
p.error {
  background: rgba(255,107,107,0.15);
  color: #ff6b6b;
  border: 1px solid rgba(255,107,107,0.3);
}
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
    <li class="dropdown">
      <a href="#" class="dropbtn" id="dropbtn">
        <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <span class="arrow">&#9662;</span>
      </a>
      <div class="dropdown-content" id="accountDropdown">
        <a href="/FoodieHub/account/edit_account.php">✏️ Edit Account</a>
        <a href="/FoodieHub/account/delete_account.php" class="danger">🗑️ Delete Account</a>
        <hr class="dropdown-divider">
        <a href="/FoodieHub/account/logout.php">🚪 Log Out</a>
      </div>
    </li>
  </ul>
</div>

<div class="edit-container">
  <h2>Edit Account</h2>

  <form method="POST" action="">

    <!-- Account Info -->
    <div class="section-label">Account Info</div>

    <label>Username</label>
    <input type="text" name="username" value="<?php echo htmlspecialchars($current_username); ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($current_email); ?>" required>

    <!-- Change Password -->
    <div class="section-label">Change Password</div>

    <label>Current Password</label>
    <input type="password" name="current_password" placeholder="Enter current password" required>

    <label>New Password</label>
    <input type="password" name="new_password" placeholder="Leave blank to keep current">
    <p class="hint">Minimum 6 characters. Leave blank if you don't want to change it.</p>

    <label>Confirm New Password</label>
    <input type="password" name="confirm_password" placeholder="Repeat new password">

    <button type="submit">Save Changes</button>

    <?php if(!empty($message)): ?>
      <p class="message <?php echo $message_type; ?>"><?php echo $message; ?></p>
    <?php endif; ?>

  </form>
</div>

<script>
  var btn = document.getElementById('dropbtn');
  var menu = document.getElementById('accountDropdown');
  if (btn && menu) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      menu.classList.toggle('open');
      btn.classList.toggle('open');
    });
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.dropdown')) {
        menu.classList.remove('open');
        btn.classList.remove('open');
      }
    });
  }
</script>

</body>
</html>
