<?php
session_start();
include 'database/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: /FoodieHub/account/account.php");
    exit();
}

$id = intval($_GET['id'] ?? 0);

// Verify ownership
$stmt = $conn->prepare("SELECT id, image FROM recipes WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();
$stmt->close();

if(!$recipe){
    echo "Recipe not found or you don't have permission.";
    exit();
}

if(isset($_POST['confirm'])){
    // Delete related data first
    $conn->query("DELETE FROM comments WHERE recipe_id=$id");
    $conn->query("DELETE FROM likes WHERE recipe_id=$id");
    $conn->query("DELETE FROM reviews WHERE recipe_id=$id");
    $conn->query("DELETE FROM recipes WHERE id=$id AND user_id=" . $_SESSION['user_id']);

    // Delete image file if exists
    if($recipe['image']){
        $img_path = 'images/recipes/' . $recipe['image'];
        if(file_exists($img_path)) unlink($img_path);
    }

    header("Location: /FoodieHub/recipes.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FoodieHub | Delete Recipe</title>
<link rel="stylesheet" href="style.css">
<style>
.delete-container { max-width: 420px; margin: 80px auto; padding: 30px; background: rgb(40,35,35); border: 1px solid rgba(220,50,50,0.3); border-radius: 12px; color: #f5f5f5; text-align: center; }
.delete-container h2 { margin-top: 0; color: #ff6b6b; }
.delete-container p { color: #aaa; font-size: 14px; margin-bottom: 24px; }
.recipe-preview { font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 8px; }
.btn-delete { padding: 11px 24px; background: #dc3545; color: white; border: none; border-radius: 7px; font-size: 15px; font-weight: 600; cursor: pointer; margin: 6px; transition: background 0.2s; }
.btn-delete:hover { background: #c82333; }
.btn-cancel { padding: 11px 24px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.15); border-radius: 7px; font-size: 15px; font-weight: 600; text-decoration: none; display: inline-block; margin: 6px; transition: background 0.2s; }
.btn-cancel:hover { background: rgba(255,255,255,0.18); }
</style>
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
  <h2>🗑️ Delete Recipe</h2>
  <p class="recipe-preview">"<?php echo htmlspecialchars($recipe['title'] ?? ''); ?>"</p>
  <p>This will permanently delete the recipe along with all its likes, reviews and comments.</p>
  <form method="POST">
    <button type="submit" name="confirm" class="btn-delete">Yes, Delete It</button>
    <a href="/FoodieHub/recipe.php?id=<?php echo $id; ?>" class="btn-cancel">Cancel</a>
  </form>
</div>

<script>
  var btn = document.getElementById('dropbtn');
  var menu = document.getElementById('accountDropdown');
  if (btn && menu) {
    btn.addEventListener('click', function(e) {
      e.preventDefault(); e.stopPropagation();
      menu.classList.toggle('open'); btn.classList.toggle('open');
    });
    document.addEventListener('click', function(e) {
      if (!e.target.closest('.dropdown')) {
        menu.classList.remove('open'); btn.classList.remove('open');
      }
    });
  }
</script>
</body>
</html>
