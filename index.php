<?php
session_start();
include 'database/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="stylesheet" type="text/css" href="style.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FoodieHub</title>
</head>
<body>

<div id="navbar">
  <div id="brand">
    <img id="logo" src="images/logo.png" alt="logo">
    <h1 id="title">FoodieHub</h1>
  </div>
  <ul>
    <li><a href="/FoodieHub/index.php" title="Home"><img src="images/home.png" alt="home"></a></li>
    <li><a href="/FoodieHub/recipes.php" title="Recipes"><img src="images/recipes.png" alt="recipes"></a></li>
    <li><a href="/FoodieHub/about/about.php" title="Info"><img src="images/info.png" alt="info"></a></li>

    <?php if(isset($_SESSION['user_id'])): ?>
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
    <?php else: ?>
    <li>
      <a href="/FoodieHub/account/account.php" title="Login">
        <img src="images/account.png" alt="account" class="account-icon">
      </a>
    </li>
    <?php endif; ?>
  </ul>
</div>

<!-- Landing Page -->
<header>
    <h1>FoodieHub</h1>
    <p>Ανακάλυψε συνταγές που θα λατρέψεις.</p>
    <div class="header-btns">
      <a href="/FoodieHub/recipes.php" class="find">Συνταγές</a>
      <a href="/FoodieHub/about/about.php" class="find">Σχετικά</a>
      <?php if(!isset($_SESSION['user_id'])): ?>
      <a href="/FoodieHub/account/account.php" class="find">Λογαριασμός</a>
      <?php endif; ?>
    </div>
</header>

<!-- Home Page -->
<div class="home-page">
    <h1>Δημοφιλείς Συνταγές</h1>
    <div class="recipe-container">
      <?php
      $sql = "SELECT r.*, u.username,
                (SELECT COUNT(*) FROM likes WHERE recipe_id = r.id) as likes,
                (SELECT ROUND(AVG(rating),1) FROM reviews WHERE recipe_id = r.id) as avg_rating
              FROM recipes r
              JOIN users u ON r.user_id = u.id
              ORDER BY likes DESC, r.id DESC";
      $result = mysqli_query($conn, $sql);
      while($row = mysqli_fetch_assoc($result)){
          $stars = $row['avg_rating'] ? $row['avg_rating'] : '—';
          echo '<div class="recipe-card">';
          echo '<img src="images/recipes/' . htmlspecialchars($row['image']) . '" class="recipe-img" alt="' . htmlspecialchars($row['title']) . '">';
          echo '<div class="recipe-card-body">';
          echo '<h2>' . htmlspecialchars($row['title']) . '</h2>';
          echo '<p>' . htmlspecialchars(substr($row['description'], 0, 80)) . '...</p>';
          echo '<div class="recipe-meta">';
          echo '<span>⏱️ ' . htmlspecialchars($row['cooking_time']) . ' min</span>';
          echo '<span>⭐ ' . $stars . '</span>';
          echo '<span>👍 ' . $row['likes'] . '</span>';
          echo '</div>';
          echo '<small class="recipe-author">by <strong>' . htmlspecialchars($row['username']) . '</strong></small>';
          echo '<a href="/FoodieHub/recipe.php?id=' . $row['id'] . '" class="btn">Δες τη συνταγή</a>';
          echo '</div>';
          echo '</div>';
      }
      ?>
    </div>
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
