<?php
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
  <script src="script.js"></script>

  <div id="navbar">
    <div id="brand">
      <img id="logo" src="images/logo.png" alt="logo">
      <h1 id="title">FoodieHub</h1>
    </div>
    <ul>
      <li><a href="/index.php"><img src="images/home.png" alt="home"></a></li>
      <li><a href="#"><img src="images/recipes.png" alt="recipes"></a></li>
      <li><a href="#"><img src="images/info.png" alt="info"></a></li>
      <li><a href="/FoodieHub/account/account.php"><img src="images/account.png" alt="account"></a></li>
    </ul>
  </div>

  <header>
    <h1>FoodieHub</h1>
    <p>Ανακάλυψε συνταγές που θα λατρέψεις.</p>
    <form>
      <input id="find" type="button" value="Σύνταγες">
      <input id="find" type="button" value="Σχετικά">
      <input id="find" type="button" value="Λογαριασμός">
    </form>
  </header>

  <div class="home-page">
    <h1>Δημοφιλείς Συνταγές</h1>

    <div class="recipe-container">
      <?php
      $sql = "SELECT r.*, u.username
              FROM recipes r
              JOIN users u ON r.user_id = u.id
              ORDER BY r.id DESC";

      $result = mysqli_query($conn, $sql);

      while($row = mysqli_fetch_assoc($result)){
          echo '<div class="recipe-card">';
          echo '<img src="images/' . htmlspecialchars($row['image']) . '" class="recipe-img" alt="' . htmlspecialchars($row['title']) . '">';
          echo '<h2>' . htmlspecialchars($row['title']) . '</h2>';
          echo '<p>' . htmlspecialchars($row['description']) . '</p>';
          echo '<small>Created by: ' . htmlspecialchars($row['username']) . '</small>';
          echo '<a href="recipe.php?id=' . $row['id'] . '" class="btn">Δες τη συνταγή</a>';
          echo '</div>';
      }
      ?>
    </div>
  </div>

</body>
</html>
