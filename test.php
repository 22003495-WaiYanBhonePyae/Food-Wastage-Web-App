<?php
$admin_password = "admin"; // Change this to your actual admin password
$hashed_password = password_hash($admin_password, PASSWORD_BCRYPT);
echo "Hashed Password: " . $hashed_password;
?>
