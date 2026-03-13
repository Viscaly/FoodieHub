<?php
session_start();
include 'database/db.php';

if(!isset($_GET['id'])){
    header("Location: /FoodieHub/index.php");
    exit();
}

$id = intval($_GET['id']);

// Handle actions (POST)
if(isset($_POST['action']) && isset($_SESSION['user_id'])){
    $action = $_POST['action'];
    $uid = $_SESSION['user_id'];

    // Like (toggle)
    if($action === 'like'){
        $chk = $conn->prepare("SELECT id FROM likes WHERE recipe_id=? AND user_id=?");
        $chk->bind_param("ii", $id, $uid);
        $chk->execute();
        $chk->bind_result($like_id);
        $chk->fetch();
        $chk->close();
        if($like_id){
            $del = $conn->prepare("DELETE FROM likes WHERE id=?");
            $del->bind_param("i", $like_id);
            $del->execute();
        } else {
            $ins = $conn->prepare("INSERT INTO likes (recipe_id, user_id) VALUES (?,?)");
            $ins->bind_param("ii", $id, $uid);
            $ins->execute();
        }
    }

    // Review (rating + comment combined)
    if($action === 'review'){
        $stars   = intval($_POST['stars']);
        $comment = trim($_POST['review_comment']);
        if($stars >= 1 && $stars <= 5 && !empty($comment)){
            // Check if already reviewed
            $chk = $conn->prepare("SELECT id FROM reviews WHERE recipe_id=? AND user_id=?");
            $chk->bind_param("ii", $id, $uid);
            $chk->execute();
            $chk->bind_result($rev_id);
            $chk->fetch();
            $chk->close();
            if($rev_id){
                $upd = $conn->prepare("UPDATE reviews SET rating=?, comment=? WHERE id=?");
                $upd->bind_param("isi", $stars, $comment, $rev_id);
                $upd->execute();
            } else {
                $ins = $conn->prepare("INSERT INTO reviews (recipe_id, user_id, comment, rating) VALUES (?,?,?,?)");
                $ins->bind_param("iisi", $id, $uid, $comment, $stars);
                $ins->execute();
            }
        }
    }

    // Comment only
    if($action === 'comment'){
        $comment = trim($_POST['comment']);
        if(!empty($comment)){
            $ins = $conn->prepare("INSERT INTO comments (recipe_id, user_id, comment) VALUES (?,?,?)");
            $ins->bind_param("iis", $id, $uid, $comment);
            $ins->execute();
        }
    }

    header("Location: /FoodieHub/recipe.php?id=$id");
    exit();
}

// Fetch recipe
$stmt = $conn->prepare("SELECT r.*, u.username FROM recipes r JOIN users u ON r.user_id = u.id WHERE r.id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$recipe = $result->fetch_assoc();
$stmt->close();

if(!$recipe){ echo "Recipe not found."; exit(); }

// Stats
$likes        = $conn->query("SELECT COUNT(*) FROM likes WHERE recipe_id=$id")->fetch_row()[0];
$avg_rating   = $conn->query("SELECT ROUND(AVG(rating),1) FROM reviews WHERE recipe_id=$id")->fetch_row()[0];
$rating_count = $conn->query("SELECT COUNT(*) FROM reviews WHERE recipe_id=$id")->fetch_row()[0];

// Current user's like & review
$user_liked  = false;
$user_review = null;
if(isset($_SESSION['user_id'])){
    $uid = $_SESSION['user_id'];
    $v = $conn->query("SELECT id FROM likes WHERE recipe_id=$id AND user_id=$uid");
    if($v && $v->num_rows > 0) $user_liked = true;
    $r = $conn->query("SELECT rating, comment FROM reviews WHERE recipe_id=$id AND user_id=$uid");
    if($r && $r->num_rows > 0) $user_review = $r->fetch_assoc();
}

// Comments
$comments = $conn->query("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.recipe_id=$id ORDER BY c.created_at DESC");

// Reviews
$reviews = $conn->query("SELECT rv.*, u.username FROM reviews rv JOIN users u ON rv.user_id = u.id WHERE rv.recipe_id=$id ORDER BY rv.created_at DESC");

$ingredients = explode("\n", $recipe['ingredients']);
$steps = isset($recipe['steps']) ? explode("\n", $recipe['steps']) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FoodieHub | <?php echo htmlspecialchars($recipe['title']); ?></title>
<link rel="stylesheet" href="style.css">
<style>
.recipe-page { max-width: 820px; margin: 40px auto 60px; padding: 0 20px; color: #f5f5f5; }
.recipe-hero img { width: 100%; max-height: 420px; object-fit: cover; border-radius: 14px; margin-bottom: 24px; }
.recipe-title { font-size: 32px; font-weight: 700; margin: 0 0 10px; }
.recipe-meta-bar { display: flex; flex-wrap: wrap; gap: 14px; margin-bottom: 24px; font-size: 14px; color: #aaa; }
.recipe-meta-bar span { display: flex; align-items: center; gap: 5px; }
.card { background: rgb(40,35,35); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 24px; margin-bottom: 20px; }
.section-title { font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #ff7a18; margin: 0 0 14px; padding-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.07); }
.ingredients-list { list-style: none; padding: 0; margin: 0; }
.ingredients-list li { padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 15px; }
.ingredients-list li::before { content: "• "; color: #ff7a18; font-weight: bold; }
.steps-list { padding: 0; margin: 0; counter-reset: steps; list-style: none; }
.steps-list li { counter-increment: steps; padding: 12px 0 12px 48px; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 15px; line-height: 1.6; position: relative; }
.steps-list li::before { content: counter(steps); position: absolute; left: 0; top: 10px; background: #ff7a18; color: #fff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; }
/* Like button */
.like-btn { padding: 10px 22px; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.2s; background: rgba(255,255,255,0.08); color: #fff; margin-bottom: 20px; }
.like-btn:hover { background: rgba(255,255,255,0.15); }
.like-btn.liked { background: #28a745; }
.like-btn:disabled { opacity: 0.5; cursor: default; }
/* Stars */
.stars-wrap { display: flex; gap: 6px; align-items: center; margin-bottom: 12px; }
.star { font-size: 28px; cursor: pointer; color: rgba(255,255,255,0.2); transition: color 0.15s, transform 0.1s; }
.star.filled { color: #ffc107; }
.star:hover { color: #ffc107; transform: scale(1.2); }
/* Comments / Reviews */
.comment, .review-item { padding: 14px 0; border-bottom: 1px solid rgba(255,255,255,0.06); }
.comment:last-child, .review-item:last-child { border-bottom: none; }
.comment-header { display: flex; justify-content: space-between; margin-bottom: 6px; }
.comment-author { font-weight: 700; font-size: 14px; color: #ff7a18; }
.comment-date { font-size: 12px; color: #666; }
.comment-text { font-size: 14px; color: #ccc; line-height: 1.6; margin: 0; }
.review-stars { color: #ffc107; font-size: 16px; margin-bottom: 4px; }
textarea { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.06); color: #fff; font-size: 14px; font-family: inherit; resize: vertical; box-sizing: border-box; }
textarea:focus { outline: none; border-color: #ff7a18; }
.submit-btn { margin-top: 10px; padding: 10px 24px; background: #ff7a18; color: #fff; border: none; border-radius: 7px; font-weight: 600; cursor: pointer; font-size: 14px; transition: background 0.2s; }
.submit-btn:hover { background: #e06615; }
.login-prompt { color: #aaa; font-size: 14px; }
.login-prompt a { color: #ff7a18; text-decoration: none; }
.empty-msg { color: #666; font-size: 14px; }
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
    <?php if(isset($_SESSION['user_id'])): ?>
    <li class="dropdown">
      <a href="#" class="dropbtn" id="dropbtn">
        <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <span class="arrow">&#9662;</span>
      </a>
      <div class="dropdown-content" id="accountDropdown">
        <a href="/FoodieHub/recipes/upload_recipe.php">🍽️ Upload Recipe</a>
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

<div class="recipe-page">

  <?php if($recipe['image']): ?>
  <div class="recipe-hero">
    <img src="images/recipes/<?php echo htmlspecialchars($recipe['image']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>">
  </div>
  <?php endif; ?>

  <h1 class="recipe-title"><?php echo htmlspecialchars($recipe['title']); ?></h1>

  <div class="recipe-meta-bar">
    <span>👨‍🍳 by <strong><?php echo htmlspecialchars($recipe['username']); ?></strong></span>
    <span>⏱️ <?php echo htmlspecialchars($recipe['cooking_time']); ?> min</span>
    <span>🍽️ <?php echo htmlspecialchars($recipe['servings']); ?> servings</span>
    <span>⭐ <?php echo $avg_rating ? $avg_rating . '/5 (' . $rating_count . ' reviews)' : 'No reviews yet'; ?></span>
    <span>👍 <?php echo $likes; ?> likes</span>
  </div>

  <!-- Description -->
  <div class="card">
    <p class="section-title">About this Recipe</p>
    <p style="color:#ccc;line-height:1.7;margin:0;"><?php echo nl2br(htmlspecialchars($recipe['description'])); ?></p>
  </div>

  <!-- Like -->
  <form method="POST">
    <input type="hidden" name="action" value="like">
    <?php if(isset($_SESSION['user_id'])): ?>
      <button class="like-btn <?php echo $user_liked ? 'liked' : ''; ?>">
        <?php echo $user_liked ? '👍 Liked!' : '👍 Like this Recipe'; ?> (<?php echo $likes; ?>)
      </button>
    <?php else: ?>
      <button class="like-btn" disabled>👍 <?php echo $likes; ?> Likes — <a href="/FoodieHub/account/account.php" style="color:#ff7a18;">Login to like</a></button>
    <?php endif; ?>
  </form>

  <!-- Ingredients -->
  <div class="card">
    <p class="section-title">Ingredients</p>
    <ul class="ingredients-list">
      <?php foreach($ingredients as $ing): ?>
        <?php if(trim($ing)): ?>
          <li><?php echo htmlspecialchars(trim($ing)); ?></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>
  </div>

  <!-- Steps -->
  <?php if(!empty($steps) && trim($steps[0])): ?>
  <div class="card">
    <p class="section-title">Instructions</p>
    <ol class="steps-list">
      <?php foreach($steps as $step): ?>
        <?php if(trim($step)): ?>
          <li><?php echo htmlspecialchars(trim($step)); ?></li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ol>
  </div>
  <?php endif; ?>

  <!-- Reviews (rating + comment) -->
  <div class="card">
    <p class="section-title">Reviews</p>

    <?php if(isset($_SESSION['user_id'])): ?>
    <form method="POST" style="margin-bottom:24px;">
      <input type="hidden" name="action" value="review">
      <input type="hidden" name="stars" id="starsInput" value="<?php echo $user_review ? $user_review['rating'] : 0; ?>">
      <div class="stars-wrap">
        <?php for($s=1; $s<=5; $s++): ?>
          <span class="star <?php echo ($user_review && $s <= $user_review['rating']) ? 'filled' : ''; ?>"
                data-val="<?php echo $s; ?>"
                onclick="setRating(<?php echo $s; ?>)">★</span>
        <?php endfor; ?>
        <span style="color:#aaa;font-size:13px;margin-left:6px;" id="ratingLabel">
          <?php echo $user_review ? 'Your rating: ' . $user_review['rating'] . '/5' : 'Select a rating'; ?>
        </span>
      </div>
      <textarea name="review_comment" rows="3" placeholder="Write your review..." style="margin-top:10px;"><?php echo $user_review ? htmlspecialchars($user_review['comment']) : ''; ?></textarea>
      <button type="submit" class="submit-btn">⭐ <?php echo $user_review ? 'Update Review' : 'Submit Review'; ?></button>
    </form>
    <?php else: ?>
      <p class="login-prompt" style="margin-bottom:20px;"><a href="/FoodieHub/account/account.php">Login</a> to leave a review.</p>
    <?php endif; ?>

    <?php if($reviews && $reviews->num_rows > 0): ?>
      <?php while($rv = $reviews->fetch_assoc()): ?>
        <div class="review-item">
          <div class="comment-header">
            <span class="comment-author"><?php echo htmlspecialchars($rv['username']); ?></span>
            <span class="comment-date"><?php echo date('d M Y', strtotime($rv['created_at'])); ?></span>
          </div>
          <div class="review-stars"><?php echo str_repeat('★', $rv['rating']) . str_repeat('☆', 5 - $rv['rating']); ?></div>
          <p class="comment-text"><?php echo nl2br(htmlspecialchars($rv['comment'])); ?></p>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="empty-msg">No reviews yet. Be the first!</p>
    <?php endif; ?>
  </div>

  <!-- Comments -->
  <div class="card">
    <p class="section-title">Comments</p>

    <?php if(isset($_SESSION['user_id'])): ?>
    <form method="POST" style="margin-bottom:24px;">
      <input type="hidden" name="action" value="comment">
      <textarea name="comment" rows="3" placeholder="Leave a quick comment..."></textarea>
      <button type="submit" class="submit-btn">💬 Post Comment</button>
    </form>
    <?php else: ?>
      <p class="login-prompt" style="margin-bottom:20px;"><a href="/FoodieHub/account/account.php">Login</a> to comment.</p>
    <?php endif; ?>

    <?php if($comments && $comments->num_rows > 0): ?>
      <?php while($c = $comments->fetch_assoc()): ?>
        <div class="comment">
          <div class="comment-header">
            <span class="comment-author"><?php echo htmlspecialchars($c['username']); ?></span>
            <span class="comment-date"><?php echo date('d M Y', strtotime($c['created_at'])); ?></span>
          </div>
          <p class="comment-text"><?php echo nl2br(htmlspecialchars($c['comment'])); ?></p>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="empty-msg">No comments yet.</p>
    <?php endif; ?>
  </div>

</div>

<script>
  function setRating(val) {
    document.getElementById('starsInput').value = val;
    document.querySelectorAll('.star').forEach(function(s) {
      s.classList.toggle('filled', parseInt(s.dataset.val) <= val);
    });
    document.getElementById('ratingLabel').textContent = 'Rating: ' + val + '/5';
  }

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
