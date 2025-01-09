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
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }

        h2 {
            text-align: center;
            margin: 20px;
            color: #495057;
        }

        table {
            width: 100%; /* Updated to take full width */
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

        td input[type="checkbox"] {
            width: 20px;
            height: 20px;
        }

        .no-data {
            text-align: center;
            font-size: 18px;
            color: #6c757d;
        }

        .back-btn {
            margin: 10px; /* Adjusted margin for better alignment */
            text-decoration: none;
            background: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background 0.3s ease;
            display: inline-block;
        }

        .back-btn:hover {
            background: #218838;
        }

        /* Legend Box */
        .legend {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .legend div {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .legend div span {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        .legend .expired {
            background: #ffcccc;
        }

        .legend .near-expiry {
            background: #fff3cd;
        }

        .legend .fresh {
            background: #d4edda;
        }
    </style>
</head>
<body>
    <h2>Your Shopping List</h2>
    <!-- Legend Section -->
    <div class="legend">
        <div><span class="expired"></span>Expired Items</div>
        <div><span class="near-expiry"></span>Expiring Soon (within 3 days)</div>
        <div><span class="fresh"></span>Fresh Items</div>
    </div>
    <?php
    session_start();

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        die("<p>Error: User not logged in. <a href='login.php'>Login</a></p>");
    }

    $user_id = $_SESSION['user_id']; // Retrieve user_id from session

    // Define a mapping for unit values
    $unit_mapping = [
        1 => 'kg',
        2 => 'g',
        3 => 'pieces',
        4 => 'ml',
        5 => 'l'
    ];

    // Connect to the database
    $host = 'localhost'; // Change if necessary
    $username = 'root'; // Change if necessary
    $password = ''; // Change if necessary
    $database = 'fyp'; // Replace with your database name

    $conn = new mysqli($host, $username, $password, $database);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch items for the logged-in user
    $sql = "SELECT * FROM groceries WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id); // Bind the user_id to the query
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<form action='generate_recipe.php' method='POST'>
                <table>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Unit</th>
                        <th>Expiry Date</th>
                        <th>Select</th>
                    </tr>";
        while ($row = $result->fetch_assoc()) {
            $unit_label = isset($unit_mapping[$row['unit']]) ? $unit_mapping[$row['unit']] : 'Unknown';

            // Calculate days until expiry
            $today = new DateTime();
            $expiry_date = new DateTime($row['expiry_date']);
            $interval = $today->diff($expiry_date)->days;

            // Determine background color based on expiry
            if ($expiry_date < $today) {
                $bg_color = 'background-color: #ffcccc;'; // Expired: Red
                $checkbox_disabled = 'disabled'; // Disable checkbox for expired items
                $checkbox_confirm = ''; // No confirmation needed for expired items
            } elseif ($interval <= 3) {
                $bg_color = 'background-color: #fff3cd;'; // Near expiry: Yellow
                $checkbox_disabled = ''; // Checkbox enabled
                $checkbox_confirm = ''; // No confirmation yet, but checkbox is enabled
            } else {
                $bg_color = 'background-color: #d4edda;'; // Fresh: Green
                $checkbox_disabled = ''; // Checkbox enabled
                $checkbox_confirm = ''; // No confirmation needed for fresh items
            }

            echo "<tr style='$bg_color'>
                    <td>" . htmlspecialchars($row['item_name']) . "</td>
                    <td>" . $row['quantity'] . "</td>
                    <td>$" . number_format($row['price'], 2) . "</td>
                    <td>" . htmlspecialchars($unit_label) . "</td>
                    <td>" . htmlspecialchars($row['expiry_date']) . "</td>
                    <td>
                        <input type='checkbox' name='selected_items[]' value='" . $row['id'] . "' $checkbox_disabled
                        onclick='return handleCheckboxClick(this, " . $interval . ", \"" . htmlspecialchars($row['expiry_date']) . "\")'>
                        $checkbox_confirm
                    </td>
                  </tr>";
        }
        echo "</table>
              <div style='display: flex; justify-content: center;'>
                  <button type='submit' class='back-btn'>Submit Selected Items</button>
                  <a href='add_items.html' class='back-btn'>Add More Items</a>
                  <a href='user_dashboard.html' class='back-btn'>Go back to dashboard</a>
              </div>
              </form>";
    } else {
        echo "<p class='no-data'>No items found in your shopping list.</p>";
    }

    $stmt->close();
    $conn->close();
    ?>

    <script>
    // Function to handle checkbox clicks
    function handleCheckboxClick(checkbox, daysUntilExpiry, expiryDate) {
        if (daysUntilExpiry <= 3) {
            // Show a Yes/No confirmation dialog
            const confirmation = confirm("This item is expiring soon (on " + expiryDate + "). Do you still want to select it?");
            if (confirmation) {
                // User selected 'Yes', keep the checkbox ticked
                return true;
            } else {
                // User selected 'No', uncheck the checkbox
                checkbox.checked = false;
                return false;
            }
        }
        // If the item is not expiring soon, allow the checkbox to be checked
        return true;
    }
    </script>

</body>
</html>
