<?php
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

// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // First, delete related records in the food_wastage table
    $conn->query("DELETE FROM food_wastage WHERE user_id = $id");

    // Then, delete the user
    $sql = "DELETE FROM users WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        $message = "User with ID $id has been deleted successfully.";
    } else {
        $message = "Error deleting user: " . $conn->error;
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

        .btn-primary, .btn-outline-primary {
            background-color: #2e7d32;
            border-color: #2e7d32;
            color: white;
            /* Ensures text is visible */
        }

        .btn-primary:hover, .btn-outline-primary:hover {
            background-color: #1b5e20;
            border-color: #1b5e20;
            color: white;
            /* Ensures text remains visible on hover */
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

        <!-- Display messages -->
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
                        <th>Password</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch all users (excluding admins)
                    $sql = "SELECT * FROM users";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['username'] . "</td>";
                            echo "<td>" . $row['email'] . "</td>";
                            // Display plain-text password
                            echo "<td>" . $row['password'] . "</td>";  // Display password in plain text
                            echo "<td>" . ($row['is_admin'] == 1 ? 'Admin' : 'User') . "</td>";
                            echo "<td>
                            <a href='edit_user.php?id=" . $row['id'] . "' class='btn btn-primary btn-sm'>Edit</a>
                                <a href='manage_user.php?delete=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</a>
                                </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>No users found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <div class="text-center mt-4">
                <a href="createUser.html" class="btn btn-outline-primary" style="color: white; background-color: #2e7d32; border-color: #2e7d32;">Add User</a>
            </div>
            <div class="text-center mt-4">
                <a href="admin_dashboard.html" class="btn btn-outline-primary" style="color: white; background-color: #2e7d32; border-color: #2e7d32;">Back to Admin Dashboard</a>
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
