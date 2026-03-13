<?php
session_start();
include '../database/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: account.php");
    exit();
}

if(isset($_POST['confirm'])){
    $uid = $_SESSION['user_id'];

    // Must delete child rows before deleting user
    $conn->query("DELETE FROM comments WHERE user_id=$uid");
    $conn->query("DELETE FROM likes WHERE user_id=$uid");
    $conn->query("DELETE FROM reviews WHERE user_id=$uid");
    $conn->query("DELETE FROM recipes WHERE user_id=$uid");
    $conn->query("DELETE FROM users WHERE id=$uid");

    session_destroy();
    header("Location: /FoodieHub/account/account.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FoodieHub | Delete Account</title>
<link rel="stylesheet" type="text/css" href="style.css">
<style>
.delete-container {
  max-width: 420px;
  margin: 50px auto;
  padding: 30px;
  background: rgb(40, 35, 35);
  border: 1px solid rgba(220, 50, 50, 0.3);
  border-radius: 12px;
  color: #f5f5f5;
  text-align: center;
}
.delete-container h2 { margin-top: 0; font-size: 20px; color: #ff6b6b; }
.delete-container p { color: #aaa; font-size: 14px; margin-bottom: 24px; }
.btn-delete { padding: 11px 24px; background: #dc3545; color: white; border: none; border-radius: 7px; font-size: 15px; font-weight: 600; cursor: pointer; transition: background 0.2s; margin: 6px; }
.btn-delete:hover { background: #c82333; }
.btn-cancel { padding: 11px 24px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.15); border-radius: 7px; font-size: 15px; font-weight: 600; cursor: pointer; transition: background 0.2s; margin: 6px; text-decoration: none; display: inline-block; }
.btn-cancel:hover { background: rgba(255,255,255,0.18); }
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
        <a href="/FoodieHub/upload_recipe.php">🍽️ Upload Recipe</a>
        <hr class="dropdown-divider">
        <a href="/FoodieHub/account/edit_account.php">✏️ Edit Account</a>
        <a href="/FoodieHub/account/delete_account.php" class="danger">🗑️ Delete Account</a>
        <hr class="dropdown-divider">
        <a href="/FoodieHub/account/logout.php">🚪 Log Out</a>
      </div>
    </li>
  </ul>
</div>

<div class="delete-container">
  <h2>⚠️ Delete Account</h2>
  <p>This action is permanent and cannot be undone. All your data will be lost.</p>
  <form method="POST" action="">
    <button type="submit" name="confirm" class="btn-delete">Yes, Delete My Account</button>
    <a href="/FoodieHub/index.php" class="btn-cancel">Cancel</a>
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
