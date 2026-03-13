<?php
session_start();
include '../database/db.php';
?>
<!DOCTYPE html>
<html lang="el">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FoodieHub | Σχετικά</title>
<link rel="stylesheet" type="text/css" href="about.css">
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
        <a href="/FoodieHub/upload_recipe.php">🍽️ Upload Recipe</a>
        <hr class="dropdown-divider">
        <a href="/FoodieHub/account/edit_account.php">✏️ Edit Account</a>
        <a href="/FoodieHub/account/delete_account.php" class="danger">🗑️ Delete Account</a>
        <hr class="dropdown-divider">
        <a href="/FoodieHub/account/logout.php">🚪 Log Out</a>
      </div>
    </li>
    <?php else: ?>
    <li><a href="/FoodieHub/account/account.php" title="Login"><img src="../images/account.png" alt="account" class="account-icon"></a></li>
    <?php endif; ?>
  </ul>
</div>

<div class="about-wrap">

  <!-- Hero -->
  <div class="about-hero">
    <h1>Τι είναι το <span>FoodieHub</span></h1>
    <div class="divider"></div>
    <p>Ένας τόπος όπου η αγάπη για το φαγητό συναντά την κοινότητα. Μαγειρέψτε, μοιραστείτε, εμπνευστείτε.</p>
  </div>

  <!-- Stats -->
  <?php
    $total_recipes = $conn->query("SELECT COUNT(*) FROM recipes")->fetch_row()[0];
    $total_users   = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
    $total_reviews = $conn->query("SELECT COUNT(*) FROM reviews")->fetch_row()[0];
  ?>
  <div class="stat-row">
    <div class="stat-box">
      <div class="stat-number"><?php echo $total_recipes; ?></div>
      <div class="stat-label">Συνταγές</div>
    </div>
    <div class="stat-box">
      <div class="stat-number"><?php echo $total_users; ?></div>
      <div class="stat-label">Μέλη</div>
    </div>
    <div class="stat-box">
      <div class="stat-number"><?php echo $total_reviews; ?></div>
      <div class="stat-label">Αξιολογήσεις</div>
    </div>
  </div>

  <!-- About -->
  <div class="about-section">
    <h2>Η ιστορία μας</h2>
    <p>Το FoodieHub ξεκίνησε από μια απλή ιδέα: να δημιουργήσουμε ένα μέρος όπου οι άνθρωποι μπορούν να μοιράζονται τις συνταγές τους, τις γεύσεις που αγαπούν και τις ιστορίες που κρύβονται πίσω από κάθε πιάτο.</p>
    <p>Πιστεύουμε ότι το φαγητό είναι κάτι παραπάνω από τροφή. Είναι γλώσσα, είναι ταυτότητα, είναι τρόπος σύνδεσης με τους ανθρώπους γύρω μας. Από την παραδοσιακή ελληνική κουζίνα μέχρι τα πιο σύγχρονα πειράματα, εδώ κάθε συνταγή έχει τη θέση της.</p>
  </div>

  <!-- Mission -->
  <div class="about-section">
    <h2>Η αποστολή μας</h2>
    <p>Θέλουμε να κάνουμε τη μαγειρική προσβάσιμη σε όλους. Δεν χρειάζεσαι να είσαι επαγγελματίας σεφ για να μοιραστείς κάτι νόστιμο. Αρκεί η επιθυμία να δώσεις κάτι από τον εαυτό σου.</p>
    <p>Κάθε συνταγή που ανεβαίνει στο FoodieHub γίνεται διαθέσιμη σε όλους, μαζί με αξιολογήσεις, σχόλια και likes από την κοινότητά μας — γιατί το καλό φαγητό αξίζει να μοιράζεται.</p>
  </div>

  <!-- Values -->
  <div class="values-grid">
    <div class="value-card">
      <h3>Κοινότητα</h3>
      <p>Χτίζουμε έναν χώρο όπου κάθε φωνή ακούγεται και κάθε συνταγή έχει αξία, ανεξάρτητα από εμπειρία ή υπόβαθρο.</p>
    </div>
    <div class="value-card">
      <h3>Αυθεντικότητα</h3>
      <p>Εκτιμούμε τις πραγματικές συνταγές, αυτές που φτιάχνουμε στα σπίτια μας, με τα υλικά που έχουμε στη διάθεσή μας.</p>
    </div>
    <div class="value-card">
      <h3>Ποιότητα</h3>
      <p>Μέσα από αξιολογήσεις και σχόλια, η κοινότητα αναδεικνύει τις καλύτερες συνταγές και βοηθά τον καθένα να βελτιώνεται.</p>
    </div>
  </div>

  <!-- CTA -->
  <div class="cta-box">
    <h2>Έτοιμος να μοιραστείς τη συνταγή σου;</h2>
    <p>Γίνε μέλος της κοινότητας του FoodieHub και ανέβασε τη δική σου συνταγή σήμερα. Είναι δωρεάν, απλό και αξίζει τον κόπο.</p>
    <?php if(isset($_SESSION['user_id'])): ?>
      <a href="/FoodieHub/upload_recipe.php" class="cta-btn">Ανέβασε Συνταγή</a>
      <a href="/FoodieHub/recipes.php" class="cta-btn secondary">Δες Συνταγές</a>
    <?php else: ?>
      <a href="/FoodieHub/account/account.php" class="cta-btn">Δημιούργησε Λογαριασμό</a>
      <a href="/FoodieHub/recipes.php" class="cta-btn secondary">Δες Συνταγές</a>
    <?php endif; ?>
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
