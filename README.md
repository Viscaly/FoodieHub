# 🍳 FoodieHub

**FoodieHub** is a community-driven recipe platform built with PHP and MySQL. Users can create accounts, upload their own recipes, discover dishes shared by others, leave reviews, ratings, likes and comments — all in a clean, dark-themed interface.

---

## ✨ Features

| Feature | Details |
|---|---|
| 👤 Authentication | Register, login, logout with session management |
| 📝 Recipe Upload | Upload recipes with image, ingredients, steps, cook time and servings |
| 🔍 Search & Sort | Filter recipes by name, sort by newest, oldest, most liked or top rated |
| ⭐ Reviews | Rate recipes 1–5 stars with a written review |
| 👍 Likes | Like your favourite recipes (one per user) |
| 💬 Comments | Leave quick comments on any recipe |
| ✏️ Edit Recipe | Recipe owners can edit their own recipes |
| 🗑️ Delete Recipe | Recipe owners can delete their recipes |
| 👤 Edit Account | Update username, email and password |
| 🗑️ Delete Account | Permanently delete account and all associated data |
| 📖 About Page | Live stats (recipes, members, reviews) pulled from the database |

---

## 🛠️ Tech Stack

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP 8.2
- **Database:** MySQL via phpMyAdmin
- **Server:** Apache (XAMPP)

---

## 📁 Project Structure

```
FoodieHub/
│
├── index.php               # Home page — featured recipes
├── recipes.php             # All recipes with search & sort
├── recipe.php              # Single recipe page (likes, reviews, comments)
├── upload_recipe.php       # Upload a new recipe (auth required)
├── edit_recipe.php         # Edit existing recipe (owner only)
├── delete_recipe.php       # Delete recipe (owner only)
├── about.php               # About page with live stats
├── style.css               # Main stylesheet
├── recipes.css             # Recipes page stylesheet
│
├── account/
│   ├── account.php         # Login page
│   ├── signup.php          # Register page
│   ├── login.php           # Login handler
│   ├── logout.php          # Logout handler
│   ├── edit_account.php    # Edit account details
│   ├── delete_account.php  # Delete account
│   └── style.css           # Account pages stylesheet
│
├── database/
│   └── db.php              # Database connection
│
└── images/
    ├── recipes/            # Uploaded recipe images
    ├── logo.png
    ├── background.png
    ├── home.png
    ├── recipes.png
    ├── info.png
    └── account.png
```

---

## 🗄️ Database Schema

**Database name:** `recipe_social`

| Table | Description |
|---|---|
| `users` | id, username, email, password, created_at |
| `recipes` | id, user_id, title, description, ingredients, steps, cooking_time, servings, image, created_at |
| `likes` | id, user_id, recipe_id |
| `reviews` | id, recipe_id, user_id, comment, rating, created_at |
| `comments` | id, user_id, recipe_id, comment, created_at |

---

## ⚙️ Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/FoodieHub.git
   ```

2. **Move to XAMPP**
   ```
   C:\xampp\htdocs\FoodieHub\
   ```

3. **Create the database**
   - Open phpMyAdmin → create database `recipe_social`
   - Import the provided SQL file or create the tables manually using the schema above

4. **Configure DB connection**
   - Edit `database/db.php` with your credentials:
   ```php
   $conn = new mysqli('localhost', 'root', '', 'recipe_social');
   ```

5. **Create image folder**
   ```
   FoodieHub/images/recipes/
   ```

6. **Start Apache & MySQL** in XAMPP and visit:
   ```
   http://localhost/FoodieHub/
   ```

---

## 📸 Screenshots

> Coming soon

---

