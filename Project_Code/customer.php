<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "beauty_parlor";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$search = "";
$category = "";

// For searching customers
if (isset($_POST['search_term']) && isset($_POST['search_category'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search_term']);
    $category = mysqli_real_escape_string($conn, $_POST['search_category']);

    // Validate selected category
    $allowed_categories = ['cust_id', 'cust_name', 'cust_phone', 'cust_mail', 'cust_loc'];
    if (in_array($category, $allowed_categories)) {
        $query = "SELECT * FROM customer WHERE $category LIKE '%$search%'";
    } else {
        $query = "SELECT * FROM customer"; // Default query
    }
} else {
    $query = "SELECT * FROM customer"; // Default query
}

$result = $conn->query($query);

// Insert new customer
if (isset($_POST['add_customer'])) {
    try {
        $conn->begin_transaction();

        // Generate customer ID
        $cust_query = "SELECT MAX(CAST(SUBSTRING(cust_id, 2) AS UNSIGNED)) AS max_id FROM customer";
        $result = $conn->query($cust_query);
        $row = $result->fetch_assoc();
        $next_id = $row['max_id'] + 1;
        $cust_id = 'C' . str_pad($next_id, 3, '0', STR_PAD_LEFT);

        // Get form data
        $cust_name = mysqli_real_escape_string($conn, $_POST['cust_name']);
        $cust_phone = mysqli_real_escape_string($conn, $_POST['cust_phone']);
        $cust_mail = mysqli_real_escape_string($conn, $_POST['cust_mail']);
        $cust_loc = mysqli_real_escape_string($conn, $_POST['cust_loc']);

        // Insert customer
        $insert_query = "INSERT INTO customer (cust_id, cust_name, cust_phone, cust_mail, cust_loc) 
                         VALUES ('$cust_id', '$cust_name', '$cust_phone', '$cust_mail', '$cust_loc')";

        if ($conn->query($insert_query) === TRUE) {
            $conn->commit();
            echo "<script>alert('Customer added successfully. ID: $cust_id');</script>";
        } else {
            throw new Exception("Error adding customer: " . $conn->error);
        }

        // Redirect to avoid resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('" . $e->getMessage() . "');</script>";
    }
}


// Delete customer
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM customer WHERE cust_id = '$delete_id'";

    if ($conn->query($delete_query) === TRUE) {
        echo "<script>alert('Customer deleted successfully');</script>";
    } else {
        echo "<script>alert('Error deleting customer: " . $conn->error . "');</script>";
    }

    // Redirect to avoid resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1, h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .search-form, .insert-form {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-input, .input-field {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 250px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .search-input:focus, .input-field:focus {
            border-color: #007BFF;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            outline: none;
        }

        .search-btn, .insert-btn {
            padding: 10px 15px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-btn:hover, .insert-btn:hover {
            background-color: #0056b3;
        }

        .search-select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .search-select:focus {
            border-color: #007BFF;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            outline: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-family: Arial, sans-serif;
        }

        th {
            background-color: #007BFF;
            color: white;
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .delete-btn {
            background-color: #f44336;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
        }

        .delete-btn:hover {
            background-color: #e53935;
        }

        .logout {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 15px;
            background-color: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .logout:hover {
            background-color: #d32f2f;
        }
        .home {
    position: absolute;
    top: 20px;
    left: 20px; /* Position it to the left side, opposite of logout */
    padding: 10px 15px;
    background-color: #4CAF50; /* Green color for home button */
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 14px;
    font-weight: bold;
    transition: background-color 0.3s ease, transform 0.3s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
}
.home:hover {
    background-color: #388E3C; /* Darker green on hover */
    transform: translateY(-3px);
}
    </style>
</head>
<body>
    <h1>Customer Management</h1>

    <!-- Search Form -->
    <form method="POST" action="" class="search-form">
        <select name="search_category" class="search-select">
            <option value="">-- Select Category --</option>
            <option value="cust_id" <?php echo ($category == 'cust_id') ? 'selected' : ''; ?>>Customer ID</option>
            <option value="cust_name" <?php echo ($category == 'cust_name') ? 'selected' : ''; ?>>Name</option>
            <option value="cust_phone" <?php echo ($category == 'cust_phone') ? 'selected' : ''; ?>>Phone</option>
            <option value="cust_mail" <?php echo ($category == 'cust_mail') ? 'selected' : ''; ?>>Email</option>
            <option value="cust_loc" <?php echo ($category == 'cust_loc') ? 'selected' : ''; ?>>Location</option>
        </select>
        <input type="text" name="search_term" placeholder="Enter search term..." value="<?php echo $search; ?>" class="search-input">
        <button type="submit" class="search-btn">Search</button>
    </form>

    <a href="logout.php" class="logout">Logout</a>
    <a href="admin_dashboard.php" class="home">Home</a>
    <!-- Insert Form -->
    <div class="container">
        <h2>Add New Customer</h2>
        <form method="POST" action="" class="insert-form">
            <input type="text" name="cust_name" placeholder="Name" class="input-field" required>
            <input type="number" name="cust_phone" placeholder="Phone" class="input-field" required>
            <input type="email" name="cust_mail" placeholder="Email" class="input-field" required>
            <input type="text" name="cust_loc" placeholder="Location" class="input-field" required>
            <button type="submit" name="add_customer" class="insert-btn">Add Customer</button>
        </form>
    </div>

    <div class="container">
        <h2>Customer List</h2>
        <table border="1" align="center">
            <tr>
                <th>Customer ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Location</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['cust_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['cust_name']); ?></td>
                    <td><?php echo $row['cust_phone']; ?></td>
                    <td><?php echo htmlspecialchars($row['cust_mail']); ?></td>
                    <td><?php echo htmlspecialchars($row['cust_loc']); ?></td>
                    <td>
                        <a href="?delete_id=<?php echo $row['cust_id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this customer?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <?php
    // Close connection
    $conn->close();
    ?>
</body>
</html>
