<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("<p>Error: User not logged in. <a href='login.php'>Login</a></p>");
}

$user_id = $_SESSION['user_id'];

// Database Connection
$conn = new mysqli('localhost', 'root', '', 'fyp');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM groceries WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping List</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        body {
            font-family: 'Roboto', sans-serif;
            background: url('food_6.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding-top: 80px; /* Prevents navbar from overlapping content */
        }

        /* Navbar Styling */
        .navbar {
            background: linear-gradient(135deg, #14961F, rgb(23, 240, 38));
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            padding: 10px 0;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-left: 20px;
            text-decoration: none;
        }

        .navbar-nav {
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .navbar-nav .nav-item {
            margin: 0 8px;
        }

        .navbar-nav .nav-link {
            color: white;
            font-size: 1.1rem;
            font-weight: bold;
            padding: 10px 15px;
            transition: all 0.3s ease-in-out;
            border-radius: 5px;
            text-decoration: none;
        }

        .navbar-nav .nav-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        /* Table Styles */
        h2 {
            text-align: center;
            margin: 20px;
            color: white;
        }

        table {
            width: 90%;
            max-width: 1000px;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 10px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background: #28a745;
            color: white;
            font-weight: bold;
        }

        td {
            color: #495057;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .back-btn {
            margin: 20px;
            text-decoration: none;
            background: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .back-btn:hover {
            background: #218838;
        }

        /* Legend Box */
        .legend {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 250px;
        }

        .legend h4 {
            margin: 0 0 10px;
            font-size: 1.1rem;
            color: #333;
        }

        .legend div {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .legend div span {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        /* Legend Colors */
        .legend .expired {
            background: #ffcccc;
        }

        .legend .near-expiry {
            background: #fff3cd;
        }

        .legend .fresh {
            background: #d4edda;
        }


        /* Edit & Delete Button Styling */
        .edit-btn, .delete-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
        }

        .edit-btn {
            background-color:rgb(21, 227, 73); 
            color: black;
        }

        .edit-btn:hover {
            background-color:rgb(139, 135, 122);
        }

        .delete-btn {
            background-color:rgb(37, 27, 225);
            color: white;
        }

        .delete-btn:hover {
            background-color:rgb(139, 135, 122);
        }

        /* Spacing between buttons */
        .btn-container {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <a class="navbar-brand" href="user_dashboard.html">
            <img src="logo.png" alt="Logo" width="35"> ⬅️ Dashboard
        </a>
        <div class="navbar-nav">
            <a class="nav-link" href="add_items.html">Add Items</a>
            <a class="nav-link active" href="view_shopping_list.php">Shopping List</a>
            <a class="nav-link" href="recipe_manage.php">Recipes</a>
            <a class="nav-link" href="cook.php">Cook</a>
            <a class="nav-link" href="calculate_wastage.html">Waste Impact</a>
            <a class="nav-link" href="generate_report.php">Reports</a>
        </div>
    </nav>

    <h2>My Groceries Tracker</h2>

    <?php
    if ($result->num_rows > 0) {
        echo "<table>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Unit</th>
                    <th>Expiry Date</th>
                    <th>Actions</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            $unit_mapping = [1 => 'kg', 2 => 'g', 3 => 'pieces', 4 => 'ml', 5 => 'l'];
            $unit_label = $unit_mapping[$row['unit']] ?? 'Unknown';

            $today = new DateTime();
            $expiry_date = new DateTime($row['expiry_date']);
            $interval = $today->diff($expiry_date)->days;

            if ($expiry_date < $today) {
                $bg_color = 'background-color: #ffcccc;';
            } elseif ($interval <= 3) {
                $bg_color = 'background-color: #fff3cd;';
            } else {
                $bg_color = 'background-color: #d4edda;';
            }

            echo "<tr style='$bg_color'>
                    <td>" . htmlspecialchars($row['item_name']) . "</td>
                    <td>" . $row['quantity'] . "</td>
                    <td>$" . number_format($row['price'], 2) . "</td>
                    <td>" . htmlspecialchars($unit_label) . "</td>
                    <td>" . htmlspecialchars($row['expiry_date']) . "</td>
                    <td>
                        <button class='edit-btn'>Edit</button>
                        <button class='delete-btn'>Delete</button>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='no-data'>No items found in your shopping list.</p>";
    }
    ?>

    <a href="add_items.html" class="back-btn">Add More Items</a>

    <!-- Legend Box (Bottom Right Corner) -->
    <div class="legend">
        <h4>Legend</h4>
        <div><span class="expired"></span> Expired Items</div>
        <div><span class="near-expiry"></span> Near Expiry (≤ 3 Days)</div>
        <div><span class="fresh"></span> Fresh Items</div>
    </div>
    

</body>
</html>
