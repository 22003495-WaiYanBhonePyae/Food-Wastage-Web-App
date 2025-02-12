<?php
header('Content-Type: application/json'); // Set response type to JSON

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Get form data
$user = trim($_POST['username']);
$email = trim($_POST['email']);
$pass = $_POST['password']; // Plain password input
$role = $_POST['role'];
$is_admin = ($role === "Admin") ? 1 : 0;

// Input validation
if (empty($user) || empty($email) || empty($pass)) {
    echo json_encode(["status" => "error", "message" => "❌ All fields are required!"]);
    exit();
}

// Validate Email Format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status" => "error", "message" => "⚠️ Invalid Email Format!"]);
    exit();
}

// Validate Password Length: At least 8 characters
if (strlen($pass) < 8) {
    echo json_encode(["status" => "error", "message" => "⚠️ Password must be at least 8 characters long!"]);
    exit();
}

// Check if email already exists
$check_email = "SELECT email FROM users WHERE email = ?";
$stmt = $conn->prepare($check_email);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "⚠️ Email already registered!"]);
    exit();
}
$stmt->close();

// Hash the Password before storing it
$hashed_password = password_hash($pass, PASSWORD_BCRYPT);

// Insert new user with hashed password
$sql = "INSERT INTO users (username, email, password, is_admin) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $user, $email, $hashed_password, $is_admin);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "🎉 User created successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error creating user: " . $conn->error]);
}

// Close connection
$stmt->close();
$conn->close();
?>
