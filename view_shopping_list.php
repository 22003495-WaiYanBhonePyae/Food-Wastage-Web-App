<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("<p>Error: User not logged in. <a href='login.html'>Login</a></p>");
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
            padding-top: 80px; 
        }

        /* Heading */
        h2 {
            text-align: center;
            margin: 20px;
            color: white;
        }

        /* Table Styling */
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

        /* Action Buttons Styling  */
        .action-buttons {
            display: flex;
            gap: 10px; 
            align-items: center;
            justify-content: center;
            white-space: nowrap; 
        }

        .action-buttons form {
            display: inline; 
        }

        /* Buttons Styling */
        .edit-btn, .delete-btn, .waste-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
            text-decoration: none;
            display: inline-block;
        }

        /* Edit Button */
        .edit-btn {
            background-color:rgb(46, 22, 182);
            color: white;
        }

        .edit-btn:hover {
            background-color:rgb(15, 46, 219);
        }


        /* Waste Button */
        .waste-btn {
            background-color: #28a745;
            color: white;
        }

        .waste-btn:hover {
            background-color: #218838;
        }

        /* Navbar Styling */
        .navbar {
            background: linear-gradient(135deg, #14961F, rgb(23, 240, 38));
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
        }

        /* Navbar Items */
        .container-fluid {
            display: flex;
            align-items: center;
            width: 100%;
            justify-content: space-between;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Centering the Nav Items */
        .navbar-nav {
            display: flex;
            gap: 15px;
            list-style: none;
            margin: 0 auto;
            padding: 0;
        }

        /* Nav Links Styling */
        .nav-link {
            color: white;
            font-size: 1.1rem;
            font-weight: bold;
            padding: 10px 15px;
            transition: all 0.3s ease-in-out;
            border-radius: 5px;
            text-decoration: none;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
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

        /* No Data Message */
        .no-data {
            color: blue;
        }

        /* Modal Styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            width: 300px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .modal-content h3 {
            margin: 0;
            color: #dc3545;
        }

        /* Modal Buttons */
        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .delete-btn {
            background-color:rgb(218, 46, 58);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .cancel-btn {
            background-color: #6c757d;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .delete-btn:hover {
            background-color:rgb(255, 0, 0);
        }

        .cancel-btn:hover {
            background-color: #545b62;
        }



    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="user_dashboard.html">
                <img src="logo.png" alt="Logo" width="35"> ⬅️Dashboard
            </a>
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="add_items.html">Add Items</a></li>
                <li class="nav-item"><a class="nav-link" href="view_shopping_list.php">Shopping List</a></li>
                <li class="nav-item"><a class="nav-link" href="recipe_manage.php">Recipes</a></li>
                <li class="nav-item"><a class="nav-link" href="cook.php">Cook</a></li>
                <li class="nav-item"><a class="nav-link" href="calculate_wastage.html">Waste Impact</a></li>
                <li class="nav-item"><a class="nav-link" href="generate_report.php">Reports</a></li>
            </ul>
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
            $unit_mapping = [1 => 'Kilogram', 2 => 'Gram', 3 => 'Pieces', 4 => 'Mililitre', 5 => 'Litre'];
            $unit_label = $unit_mapping[$row['unit']] ?? 'Unknown';

            $today = new DateTime();
            $expiry_date = new DateTime($row['expiry_date']);
            $interval = $today->diff($expiry_date)->days;

            // Assign background color based on expiry status
            if ($expiry_date < $today) {
                $bg_color = 'background-color: #ffcccc;'; // Expired item will be displayed with red bg color
            } elseif ($interval <= 3) {
                $bg_color = 'background-color: #fff3cd;'; // Near Expiry will be displayed with yellow bg color
            } else {
                $bg_color = 'background-color: #d4edda;'; // Fresh items are displayed with green bg color
            }

            echo "<tr style='$bg_color'>
                <td>" . htmlspecialchars($row['item_name']) . "</td>
                <td>" . $row['quantity'] . "</td>
                <td>$" . number_format($row['price'], 2) . "</td>
                <td>" . htmlspecialchars($unit_label) . "</td>
                <td>" . htmlspecialchars($row['expiry_date']) . "</td>
                <td class='action-buttons'>
                    <a href='edit_item.php?id=" . $row['id'] . "' class='edit-btn'>Edit</a>

                    <form class='delete-form' action='delete_item.php' method='POST'>
                        <input type='hidden' name='id' value='" . $row['id'] . "'>
                        <button type='button' class='delete-btn open-modal' data-id='" . $row['id'] . "'>
                            Delete
                        </button>
                    </form>

                    <a href='calculate_wastage.html?
                    item=" . urlencode($row['item_name']) . "
                    &type=" . urlencode($row['category']) . "
                    &quantity=" . urlencode($row['quantity']) . "
                    &unit=" . urlencode($unit_label) . "' 
                    class='waste-btn'>Waste</a>
                </td>

            </tr>";

        }
        echo "</table>";
    } else {
        echo "<p class='no-data'>No items found in your shopping list.</p>";
    }

    $stmt->close();
    $conn->close();
    ?>
    <!-- Legend Box (Bottom Right Corner) -->
    <div class="legend">
        <h4>Legend</h4>
        <div><span class="expired"></span> Expired Items</div>
        <div><span class="near-expiry"></span> Near Expiry (≤ 3 Days)</div>
        <div><span class="fresh"></span> Fresh Items</div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Deletion</h3>
            <p>Are you sure you want to delete this item?</p>
            <button id="confirmDelete" class="delete-btn">Yes, Delete</button>
            <button id="cancelDelete" class="cancel-btn">Cancel</button>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let deleteModal = document.getElementById("deleteModal");
            let confirmDelete = document.getElementById("confirmDelete");
            let cancelDelete = document.getElementById("cancelDelete");
            let deleteForm = null;

            document.querySelectorAll(".open-modal").forEach(button => {
                button.addEventListener("click", function() {
                    deleteForm = this.closest(".delete-form");
                    deleteModal.style.display = "flex";
                });
            });

            confirmDelete.addEventListener("click", function() {
                if (deleteForm) {
                    deleteForm.submit(); 
                }
            });

            cancelDelete.addEventListener("click", function() {
                deleteModal.style.display = "none";
            });

            window.addEventListener("click", function(event) {
                if (event.target === deleteModal) {
                    deleteModal.style.display = "none";
                }
            });
        });
        document.addEventListener("DOMContentLoaded", () => {
            fetch('check_expiry.php')
                .then(response => response.text())
                .then(data => console.log("✅ Expiry check completed:", data))
                .catch(error => console.error("❌ Expiry check failed:", error));
        });
        </script>


    

    
</body>
</html>
