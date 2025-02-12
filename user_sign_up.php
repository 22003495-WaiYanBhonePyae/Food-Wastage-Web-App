<?php
header('Content-Type: application/json'); // Set response type to JSON

// Secure Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "❌ Connection failed: " . $conn->connect_error]);
    exit();
}

// Check if form data is set
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST['username']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    // Input Validation
    if (empty($user) || empty($email) || empty($pass)) {
        echo json_encode(["status" => "error", "message" => "❌ All fields are required!"]);
        exit();
    }

    // Validate Email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "⚠️ Invalid Email Format!"]);
        exit();
    }

    // Validate Password Length: must be at least 8 characters
    if (strlen($pass) < 8) {
        echo json_encode(["status" => "error", "message" => "❌ Password must be at least 8 characters long!"]);
        exit();
    }

    // Check if Email Already Exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "⚠️ Email already registered! Please log in."]);
        exit();
    }
    $check_stmt->close();

    // Hash the Password before storing it
    $hashed_password = password_hash($pass, PASSWORD_BCRYPT);

    // Assign Role
    $is_admin = 0;

    // Use Prepared Statements to Prevent SQL Injection
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $user, $email, $hashed_password, $is_admin);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "🎉 Account created successfully! You can now log in."]);
    } else {
        echo json_encode(["status" => "error", "message" => "❌ Error Creating Account!"]);
    }

    // Close Statement & Connection
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "❌ Invalid Request!"]);
}
?>
