<?php
// Start session
session_start();

// Secure Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fyp";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form data is set
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $pass = $_POST['password']; 

    // Input Validation
    if (empty($email) || empty($pass)) {
        $_SESSION['message'] = "❌ Error: Email and password are required!";
        header("Location: login.php");
        exit();
    } else {
        // Use Prepared Statement to Prevent SQL Injection
        $stmt = $conn->prepare("SELECT id, username, password, is_admin FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $username, $hashed_pass, $is_admin);
            $stmt->fetch();

            // ✅ Correct way: Use password_verify() to check hashed password
            if (password_verify($pass, $hashed_pass)) {
                // Store user details in session
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['is_admin'] = $is_admin;

                // Redirect based on user role
                if ($is_admin == 1) {
                    header("Location: admin_dashboard.html");
                } else {
                    header("Location: user_dashboard.html");
                }
                exit();
            } else {
                $_SESSION['message'] = "⚠️ Invalid password. Please try again.";
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "⚠️ No account found with that email.";
            header("Location: login.php");
            exit();
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const message = "<?php echo isset($_SESSION['message']) ? htmlspecialchars($_SESSION['message'], ENT_QUOTES, 'UTF-8') : ''; ?>";
            
            // Clear PHP session message
            <?php unset($_SESSION['message']); ?>

            if (message) {
                const modalHTML = `
                    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title text-danger" id="errorModalLabel">Error</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    ${message}
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                document.body.insertAdjacentHTML('beforeend', modalHTML);
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();

                const modalElement = document.getElementById('errorModal');
                modalElement.addEventListener('hidden.bs.modal', () => {
                    window.location.href = 'login.html';
                });
            } else {
                window.location.href = 'login.html';
            }
        });
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
