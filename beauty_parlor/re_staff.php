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
    $allowed_categories = ['staff_id', 'staff_name', 'staff_phone', 'staff_mail', 'staff_role'];
    if (in_array($category, $allowed_categories)) {
        $query = "SELECT * FROM staff WHERE $category LIKE '%$search%'";
    } else {
        $query = "SELECT * FROM staff"; // Default query if category is invalid
    }
} else {
    $query = "SELECT * FROM staff"; // Default query to fetch all records
}

$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management</title>
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
            background-color: rgb(145, 98, 199);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-btn:hover, .insert-btn:hover {
            background-color: rgb(149, 0, 179);
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
            background-color: rgb(145, 98, 199);
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
    <h1>Welcome to Staff Management</h1>

    <!-- Search Form -->
    <form method="POST" action="" class="search-form">
        <select name="search_category" class="search-select">
            <option value="">-- Select Category --</option>
            <option value="staff_id" <?php echo ($category == 'staff_id') ? 'selected' : ''; ?>>Staff ID</option>
            <option value="staff_name" <?php echo ($category == 'staff_name') ? 'selected' : ''; ?>>Name</option>
            <option value="staff_phone" <?php echo ($category == 'staff_phone') ? 'selected' : ''; ?>>Phone</option>
            <option value="staff_mail" <?php echo ($category == 'staff_mail') ? 'selected' : ''; ?>>Email</option>
            <option value="staff_role" <?php echo ($category == 'staff_role') ? 'selected' : ''; ?>>Role</option>
        </select>
        <input type="text" name="search_term" placeholder="Enter search term..." value="<?php echo $search; ?>" class="search-input">
        <button type="submit" class="search-btn">Search</button>
    </form>

    <a href="logout.php" class="logout">Logout</a>
    <a href="receptionist_dashboard.php" class="home">Home</a>

    <div class="container">
        <h2>Staff List</h2>
        <table border="1" align="center">
            <tr>
                <th>Staff ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Position</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['staff_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['staff_name']); ?></td>
                    <td><?php echo $row['staff_phone']; ?></td>
                    <td><?php echo htmlspecialchars($row['staff_mail']); ?></td>
                    <td><?php echo htmlspecialchars($row['staff_role']); ?></td>
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
