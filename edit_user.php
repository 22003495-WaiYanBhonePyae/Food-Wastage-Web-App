<?php
session_start();

// Check if the user is logged in; if not, redirect to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

// Database connection parameters
$servername  = "localhost";
$db_username = "root";
$db_password = "";
$dbname      = "fyp";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variables
$message   = "";
$alertType = ""; // Use "alert-danger" for errors and "alert-success" for successes

// Fetch user details to pre-populate the form if an ID is provided in the URL
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $sql  = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        $message   = "User not found.";
        $alertType = "alert-danger";
    }
} else {
    $message   = "No user specified.";
    $alertType = "alert-danger";
}

// Process the form submission to update user details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and trim form fields
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $is_admin = isset($_POST['is_admin']) ? (int)$_POST['is_admin'] : 0;

    // Server-side validation for empty fields and password length
    if (empty($username) || empty($email) || empty($password)) {
        $message   = "Please fill in all required fields.";
        $alertType = "alert-danger";
    } elseif (strlen($password) < 8) {
        $message   = "Password must be at least 8 characters long.";
        $alertType = "alert-danger";
    } else {
        // Check for duplicate email (exclude the current user)
        $sqlCheck = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("si", $email, $user_id);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        if ($resultCheck->num_rows > 0) {
            $message   = "Email already exists.";
            $alertType = "alert-danger";
        } else {
            // Prepare the update query
            $sqlUpdate = "UPDATE users SET username = ?, email = ?, password = ?, is_admin = ? WHERE id = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("sssii", $username, $email, $password, $is_admin, $user_id);

            if ($stmtUpdate->execute()) {
                $message   = "User updated successfully.";
                $alertType = "alert-success";
                // Update the user array for form re-population
                $user['username'] = $username;
                $user['email']    = $email;
                $user['password'] = $password;
                $user['is_admin'] = $is_admin;
            } else {
                $message   = "Error updating user: " . $conn->error;
                $alertType = "alert-danger";
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit User</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f8f9fa;
      font-family: Arial, sans-serif;
    }
    .container {
      max-width: 600px;
      margin: 50px auto;
      background-color: #ffffff;
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #2e7d32;
    }
    .form-control {
      border-radius: 8px;
      margin-bottom: 20px;
    }
    .btn-primary {
      background-color: #2e7d32;
      border-color: #2e7d32;
    }
    .btn-primary:hover {
      background-color: #1b5e20;
      border-color: #1b5e20;
    }
    .alert {
      margin-bottom: 30px;
    }
    /* Styling for password toggle */
    .input-group-text {
      cursor: pointer;
      background: #ffffff;
      border-left: none;
    }
    .input-group input {
      border-right: none;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Edit User</h2>

    <!-- Display message box -->
    <?php if (!empty($message)) { ?>
      <div class="alert <?php echo $alertType; ?> text-center"><?php echo $message; ?></div>
    <?php } ?>

    <!-- Edit User Form -->
    <form method="POST" action="">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input
          type="text"
          class="form-control"
          id="username"
          name="username"
          value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>"
          required
        >
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input
          type="email"
          class="form-control"
          id="email"
          name="email"
          value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
          required
        >
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
          <input
            type="password"
            class="form-control"
            id="password"
            name="password"
            value="<?php echo htmlspecialchars($user['password'] ?? ''); ?>"
            required
          >
          <span class="input-group-text" id="togglePassword">👁️</span>
        </div>
        <small class="text-muted">Password must be at least 8 characters long.</small>
      </div>
      <div class="mb-3">
        <label for="is_admin" class="form-label">User Role</label>
        <select class="form-control" id="is_admin" name="is_admin" required>
          <option value="0" <?php if (isset($user['is_admin']) && $user['is_admin'] == 0) echo 'selected'; ?>>User</option>
          <option value="1" <?php if (isset($user['is_admin']) && $user['is_admin'] == 1) echo 'selected'; ?>>Admin</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary w-100">Save Changes</button>
      <a href="manage_user.php" class="btn btn-secondary w-100 mt-3">Back to User List</a>
    </form>
  </div>

  <!-- JavaScript to toggle password visibility -->
  <script>
    document.getElementById("togglePassword").addEventListener("click", function() {
      let passwordField = document.getElementById("password");
      if (passwordField.type === "password") {
        passwordField.type = "text";
        this.textContent = "🙈"; // Change icon to indicate hiding
      } else {
        passwordField.type = "password";
        this.textContent = "👁️"; // Change icon to indicate showing
      }
    });
  </script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
