<?php
session_start(); // Start session to track logged-in admin

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize message variable
$message = "";

// Get the logged-in admin ID
$logged_in_admin_id = $_SESSION['user_id'] ?? 0; // Ensure session is set

// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Prevent the admin from deleting themselves
    if ($id == $logged_in_admin_id) {
        $message = "❌ You cannot delete your own account!";
    } else {
        // Retrieve the username before deletion
        $sqlUser = "SELECT username FROM users WHERE id = ?";
        $stmtUser = $conn->prepare($sqlUser);
        $stmtUser->bind_param("i", $id);
        $stmtUser->execute();
        $resultUser = $stmtUser->get_result();
        
        if ($resultUser->num_rows > 0) {
            $row = $resultUser->fetch_assoc();
            $usernameDeleted = $row['username'];

            // First, delete related records in the food_wastage table
            $conn->query("DELETE FROM food_wastage WHERE user_id = $id");

            // Then, delete the user
            $sqlDelete = "DELETE FROM users WHERE id = ?";
            $stmtDelete = $conn->prepare($sqlDelete);
            $stmtDelete->bind_param("i", $id);

            if ($stmtDelete->execute()) {
                $message = "✅ User <strong>$usernameDeleted</strong> has been deleted successfully.";
            } else {
                $message = "❌ Error deleting user: " . $conn->error;
            }
        } else {
            $message = "⚠️ User not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - User Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f3f9f3;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
        }

        .table-container {
            margin-top: 20px;
        }

        h2 {
            color: #2e7d32;
        }

        .btn-primary,
        .btn-outline-primary {
            background-color: #2e7d32;
            border-color: #2e7d32;
            color: white;
        }

        .btn-primary:hover,
        .btn-outline-primary:hover {
            background-color: #1b5e20;
            border-color: #1b5e20;
            color: white;
        }

        .btn-danger {
            background-color: #d32f2f;
            border-color: #d32f2f;
        }

        .btn-danger:hover {
            background-color: #b71c1c;
            border-color: #b71c1c;
        }

        .table thead {
            background-color: #2e7d32;
            color: white;
        }

        .alert-info {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-color: #a5d6a7;
        }

        .text-center a {
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="text-center mb-4">Admin Panel - User List</h2>

        <!-- Display messages in an alert box -->
        <?php if (!empty($message)) { ?>
            <div class="alert alert-info text-center"><?php echo $message; ?></div>
        <?php } ?>

        <!-- User List Table -->
        <div class="table-container">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch all users
                    $sql = "SELECT * FROM users";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['username'] . "</td>";
                            echo "<td>" . $row['email'] . "</td>";
                            echo "<td>" . ($row['is_admin'] == 1 ? 'Admin' : 'User') . "</td>";
                            echo "<td>";
                            
                            // Prevent the logged-in admin from deleting themselves
                            if ($row['id'] == $logged_in_admin_id) {
                                echo "<a href='edit_user.php?id=" . $row['id'] . "' class='btn btn-primary btn-sm'>Edit</a> ";
                                echo "<button class='btn btn-danger btn-sm' disabled>Cannot Delete</button>";
                            } else {
                                echo "<a href='edit_user.php?id=" . $row['id'] . "' class='btn btn-primary btn-sm'>Edit</a> ";
                                echo "<a href='manage_user.php?delete=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</a>";
                            }

                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>No users found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <div class="text-center mt-4">
                <a href="createUser.html" class="btn btn-outline-primary">Add User</a>
            </div>
            <div class="text-center mt-4">
                <a href="admin_dashboard.html" class="btn btn-outline-primary">Back to Admin Dashboard</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>
