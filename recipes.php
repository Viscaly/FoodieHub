<?php
session_start();
include 'database/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FoodieHub | Recipes</title>
<link rel="stylesheet" type="text/css" href="recipes.css">
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
    <li><a href="/FoodieHub/account/account.php" title="Login"><img src="images/account.png" alt="account" class="account-icon"></a></li>
    <?php endif; ?>
  </ul>
</div>

<header>
  <h1>Συνταγές</h1>
  <p>Ανακάλυψε όλες τις συνταγές μας.</p>
</header>

<div class="home-page">

  <!-- Search & Filter -->
  <div class="filters">
    <input type="text" id="searchInput" placeholder="🔍 Search recipes..." onkeyup="filterRecipes()">
    <select id="sortSelect" onchange="sortRecipes()">
      <option value="newest">Newest First</option>
      <option value="oldest">Oldest First</option>
      <option value="likes">Most Liked</option>
      <option value="rating">Top Rated</option>
    </select>
  </div>

  <div class="recipe-container" id="recipeGrid">
    <?php
    $sql = "SELECT r.*, u.username,
              (SELECT COUNT(*) FROM likes WHERE recipe_id = r.id) as likes,
              (SELECT ROUND(AVG(rating),1) FROM reviews WHERE recipe_id = r.id) as avg_rating
            FROM recipes r
            JOIN users u ON r.user_id = u.id
            ORDER BY r.id DESC";
    $result = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($result)):
        $stars = $row['avg_rating'] ? number_format($row['avg_rating'], 1) : '—';
        $is_owner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id'];
    ?>
      <div class="recipe-card"
           data-title="<?php echo strtolower(htmlspecialchars($row['title'])); ?>"
           data-likes="<?php echo $row['likes']; ?>"
           data-rating="<?php echo $row['avg_rating'] ?? 0; ?>"
           data-date="<?php echo $row['id']; ?>">

        <div class="recipe-img-wrap">
          <img src="images/recipes/<?php echo htmlspecialchars($row['image']); ?>" class="recipe-img" alt="<?php echo htmlspecialchars($row['title']); ?>">
          <?php if($is_owner): ?>
          <div class="owner-actions">
            <a href="/FoodieHub/edit_recipe.php?id=<?php echo $row['id']; ?>" class="owner-btn edit">✏️</a>
            <a href="/FoodieHub/delete_recipe.php?id=<?php echo $row['id']; ?>" class="owner-btn delete" onclick="return confirm('Delete this recipe?')">🗑️</a>
          </div>
          <?php endif; ?>
        </div>

        <div class="recipe-card-body">
          <h2><?php echo htmlspecialchars($row['title']); ?></h2>
          <p><?php echo htmlspecialchars(substr($row['description'], 0, 80)); ?>...</p>
          <div class="recipe-meta">
            <span>⏱️ <?php echo htmlspecialchars($row['cooking_time']); ?> min</span>
            <span>⭐ <?php echo $stars; ?></span>
            <span>👍 <?php echo $row['likes']; ?></span>
          </div>
          <small class="recipe-author">by <strong><?php echo htmlspecialchars($row['username']); ?></strong></small>
          <a href="/FoodieHub/recipe.php?id=<?php echo $row['id']; ?>" class="btn">Δες τη συνταγή</a>
        </div>
      </div>
    <?php endwhile; ?>
  </div>

  <?php if(mysqli_num_rows($result) === 0): ?>
    <p style="color:#aaa;text-align:center;margin-top:40px;">No recipes yet. <a href="/FoodieHub/upload_recipe.php" style="color:#ff7a18;">Be the first to upload one!</a></p>
  <?php endif; ?>

</div>

<script>
  function filterRecipes() {
    var input = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('.recipe-card').forEach(function(card) {
      card.style.display = card.dataset.title.includes(input) ? '' : 'none';
    });
  }

  function sortRecipes() {
    var sort = document.getElementById('sortSelect').value;
    var grid = document.getElementById('recipeGrid');
    var cards = Array.from(grid.querySelectorAll('.recipe-card'));

    cards.sort(function(a, b) {
      if (sort === 'likes')  return b.dataset.likes - a.dataset.likes;
      if (sort === 'rating') return b.dataset.rating - a.dataset.rating;
      if (sort === 'oldest') return a.dataset.date - b.dataset.date;
      return b.dataset.date - a.dataset.date; // newest
    });

    cards.forEach(function(card) { grid.appendChild(card); });
  }

  // Dropdown
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
