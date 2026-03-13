<?php
session_start();
include 'database/db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: /FoodieHub/account/account.php");
    exit();
}

$id = intval($_GET['id'] ?? 0);

// Fetch recipe and verify ownership
$stmt = $conn->prepare("SELECT * FROM recipes WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();
$stmt->close();

if(!$recipe){
    echo "Recipe not found or you don't have permission to edit it.";
    exit();
}

$message = "";
$message_type = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title        = trim($_POST['title']);
    $description  = trim($_POST['description']);
    $cooking_time = trim($_POST['cooking_time']);
    $servings     = intval($_POST['servings']);
    $ingredients  = trim($_POST['ingredients']);
    $steps        = trim($_POST['steps']);
    $image_name   = $recipe['image']; // keep existing by default

    if(isset($_FILES['image']) && $_FILES['image']['error'] === 0){
        $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
        if(in_array($_FILES['image']['type'], $allowed)){
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid('recipe_') . '.' . $ext;
            $upload_dir = 'images/recipes/';
            if(!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
        }
    }

    if(empty($title) || empty($description) || empty($ingredients)){
        $message = "Title, description and ingredients are required.";
        $message_type = "error";
    } else {
        $stmt = $conn->prepare("UPDATE recipes SET title=?, description=?, ingredients=?, steps=?, cooking_time=?, servings=?, image=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sssssisii", $title, $description, $ingredients, $steps, $cooking_time, $servings, $image_name, $id, $_SESSION['user_id']);
        if($stmt->execute()){
            $stmt->close();
            header("Location: /FoodieHub/recipe.php?id=$id");
            exit();
        } else {
            $message = "Error: " . $stmt->error;
            $message_type = "error";
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FoodieHub | Edit Recipe</title>
<link rel="stylesheet" href="style.css">
<style>
.upload-wrap { max-width: 700px; margin: 40px auto 60px; padding: 0 20px; }
.upload-wrap h2 { color: #fff; font-size: 26px; margin-bottom: 24px; }
.card { background: rgb(40,35,35); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 28px; margin-bottom: 20px; }
.section-title { font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #ff7a18; margin: 0 0 16px; padding-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.07); }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: #aaa; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.4px; }
.form-group input[type=text],
.form-group input[type=number],
.form-group textarea { width: 100%; padding: 10px 12px; border-radius: 7px; border: 1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.06); color: #fff; font-size: 14px; font-family: inherit; box-sizing: border-box; resize: vertical; transition: border-color 0.2s; }
.form-group input:focus, .form-group textarea:focus { outline: none; border-color: #ff7a18; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.current-img { width: 100%; max-height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 12px; }
.image-upload-area { border: 2px dashed rgba(255,255,255,0.15); border-radius: 10px; padding: 20px; text-align: center; cursor: pointer; transition: border-color 0.2s; position: relative; }
.image-upload-area:hover { border-color: #ff7a18; }
.image-upload-area input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
.image-upload-area p { color: #aaa; margin: 0; font-size: 14px; }
#preview-img { max-width: 100%; max-height: 200px; border-radius: 8px; margin-top: 12px; display: none; }
.submit-btn { width: 100%; padding: 14px; background: #ff7a18; color: #fff; border: none; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; transition: background 0.2s; }
.submit-btn:hover { background: #e06615; }
.message { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; font-weight: 600; }
.message.error { background: rgba(255,107,107,0.15); color: #ff6b6b; border: 1px solid rgba(255,107,107,0.3); }
.required { color: #ff7a18; }
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

<div class="upload-wrap">
  <h2>✏️ Edit Recipe</h2>

  <?php if(!empty($message)): ?>
    <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
  <?php endif; ?>

  <form method="POST" action="" enctype="multipart/form-data">

    <div class="card">
      <p class="section-title">Basic Info</p>
      <div class="form-group">
        <label>Title <span class="required">*</span></label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($recipe['title']); ?>" required>
      </div>
      <div class="form-group">
        <label>Description <span class="required">*</span></label>
        <textarea name="description" rows="3"><?php echo htmlspecialchars($recipe['description']); ?></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Cook Time (minutes)</label>
          <input type="text" name="cooking_time" value="<?php echo htmlspecialchars($recipe['cooking_time']); ?>">
        </div>
        <div class="form-group">
          <label>Servings</label>
          <input type="number" name="servings" min="1" max="100" value="<?php echo htmlspecialchars($recipe['servings']); ?>">
        </div>
      </div>
    </div>

    <div class="card">
      <p class="section-title">Photo</p>
      <?php if($recipe['image']): ?>
        <img src="images/recipes/<?php echo htmlspecialchars($recipe['image']); ?>" class="current-img" alt="Current photo">
      <?php endif; ?>
      <div class="image-upload-area">
        <p>📷 Click to upload a new photo (leave empty to keep current)</p>
        <input type="file" name="image" accept="image/*" onchange="previewImage(this)">
      </div>
      <img id="preview-img" src="" alt="Preview">
    </div>

    <div class="card">
      <p class="section-title">Ingredients <span class="required">*</span></p>
      <div class="form-group">
        <label>One ingredient per line</label>
        <textarea name="ingredients" rows="8"><?php echo htmlspecialchars($recipe['ingredients']); ?></textarea>
      </div>
    </div>

    <div class="card">
      <p class="section-title">Steps</p>
      <div class="form-group">
        <label>One step per line</label>
        <textarea name="steps" rows="8"><?php echo htmlspecialchars($recipe['steps'] ?? ''); ?></textarea>
      </div>
    </div>

    <button type="submit" class="submit-btn">💾 Save Changes</button>
  </form>
</div>

<script>
  function previewImage(input) {
    var preview = document.getElementById('preview-img');
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) { preview.src = e.target.result; preview.style.display = 'block'; };
      reader.readAsDataURL(input.files[0]);
    }
  }
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
