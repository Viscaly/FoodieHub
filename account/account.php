<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FoodieHub | Λογαριασμός</title>
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

    <?php if(isset($_SESSION['user_id'])): ?>
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
    <?php else: ?>
    <li>
      <a href="/FoodieHub/account/account.php" title="Login">
        <img src="../images/account.png" alt="account" class="account-icon">
      </a>
    </li>
    <?php endif; ?>
  </ul>
</div>

<header>
    <h1>Λογαριασμός</h1>
</header>

<div class="login">
    <form method="POST" action="login.php">
        <h3 id="login">LOGIN</h3>

        <label for="email">EMAIL</label><br>
        <input type="email" name="email" id="email" placeholder="your@email.com" required><br>

        <label for="password">PASSWORD</label><br>
        <input type="password" name="password" id="password" placeholder="*******" required><br>

        <button type="submit" id="btnlogin">Login</button>
        <p>Do not have an account? <a href="signup.php">Sign Up</a></p>

        <?php
        if(isset($_GET['error'])){
            echo "<p class='error'>Invalid email or password</p>";
        }
        if(isset($_GET['signup']) && $_GET['signup'] === 'success'){
            echo "<p class='success'>Account created successfully! Please log in.</p>";
        }
        ?>
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
