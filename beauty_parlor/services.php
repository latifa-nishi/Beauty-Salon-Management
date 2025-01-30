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

$search = "";
$category = "";

// For searching data
if (isset($_POST['search_term']) && isset($_POST['search_category'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search_term']); // Sanitize input
    $category = mysqli_real_escape_string($conn, $_POST['search_category']); // Sanitize category

    // Validate the selected category against allowed columns
    $allowed_categories = ['sid', 'sname', 'sprice', 'sduration'];
    if (in_array($category, $allowed_categories)) {
        $query = "SELECT * FROM services WHERE $category LIKE '%$search%'";
    } else {
        $query = "SELECT * FROM services"; // Default query if category is invalid
    }
} else {
    $query = "SELECT * FROM services"; // Default query to fetch all records
}

$result = $conn->query($query);


// Insert service
if (isset($_POST['add_service'])) {
    try {
        $conn->begin_transaction();

        // Generate service ID
        $sid_query = "SELECT MAX(CAST(SUBSTRING(sid, 2) AS UNSIGNED)) AS max_id FROM services";
        $result = $conn->query($sid_query);
        $row = $result->fetch_assoc();
        $next_id = $row['max_id'] + 1;
        $sid = 'S' . str_pad($next_id, 3, '0', STR_PAD_LEFT);

        // Get form data
        $sname = mysqli_real_escape_string($conn, $_POST['sname']);
        $sprice = mysqli_real_escape_string($conn, $_POST['sprice']);
        $sduration = mysqli_real_escape_string($conn, $_POST['sduration']); // Duration in minutes

        // Convert minutes to HH:MM:SS format
        $hours = floor($sduration / 60); // Calculate hours
        $minutes = $sduration % 60;     // Calculate remaining minutes
        $formatted_duration = sprintf("%02d:%02d:00", $hours, $minutes); // Format as HH:MM:00

        // Insert into the database
        $insert_query = "INSERT INTO services (sid, sname, sprice, sduration) 
                         VALUES ('$sid', '$sname', '$sprice', '$formatted_duration')";

        if ($conn->query($insert_query) === TRUE) {
            $conn->commit();
            echo "<script>alert('Service added successfully. ID: $sid');</script>";
        } else {
            throw new Exception("Error adding service: " . $conn->error);
        }

        // Redirect to avoid resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('" . $e->getMessage() . "');</script>";
    }
}



// Delete service
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id']; // Get the ID to delete
    $delete_query = "DELETE FROM services WHERE sid = '$delete_id'";

    if ($conn->query($delete_query) === TRUE) {
        echo "<script>alert('Service deleted successfully');</script>";
    } else {
        echo "<script>alert('Error deleting service: " . $conn->error . "');</script>";
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
    <title>Services Management</title>
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
    <h1>Welcome to Services Management</h1>

    <!-- Search Form -->
    <form method="POST" action="" class="search-form">
        <select name="search_category" class="search-select">
            <option value="">-- Select Category --</option>
            <option value="sid" <?php echo ($category == 'sid') ? 'selected' : ''; ?>>Service ID</option>
            <option value="sname" <?php echo ($category == 'sname') ? 'selected' : ''; ?>>Name</option>
            <option value="sprice" <?php echo ($category == 'sprice') ? 'selected' : ''; ?>>Price</option>
            <option value="sduration" <?php echo ($category == 'sduration') ? 'selected' : ''; ?>>Duration</option>
        </select>
        <input type="text" name="search_term" placeholder="Enter search term..." value="<?php echo $search; ?>" class="search-input">
        <button type="submit" class="search-btn">Search</button>
    </form>

    <a href="logout.php" class="logout">Logout</a>
    <a href="admin_dashboard.php" class="home">Home</a>
    <!-- Insert Service Form -->
    <div class="container">
        <h2>Add New Service</h2>
        <form method="POST" action="" class="insert-form">
            <input type="text" name="sname" placeholder="Name" class="input-field" required>
            <input type="number" step="0.01" name="sprice" placeholder="Price" class="input-field" required>
            <input type="number" name="sduration" placeholder="Duration (minutes)" class="input-field" required>
            <button type="submit" name="add_service" class="insert-btn">Add Service</button>
        </form>
    </div>

    <div class="container">
        <h2>Services List</h2>
        <table border="1" align="center">
            <tr>
                <th>Service ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Duration</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['sid']); ?></td>
                    <td><?php echo htmlspecialchars($row['sname']); ?></td>
                    <td><?php echo $row['sprice']; ?></td>
                    <td><?php echo $row['sduration']; ?></td>
                    <td>
                        <a href="?delete_id=<?php echo $row['sid']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this service?')">Delete</a>
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
