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

// For searching data
$search = '';
$category = '';
if (isset($_POST['search']) && isset($_POST['category'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search']); // Sanitize input
    $category = $_POST['category']; // Get selected category

    // Build the WHERE clause based on the selected category
    switch ($category) {
        case 'rating':
            $where_clause = "review.rating LIKE '%$search%'";
            break;
        case 'customer':
            $where_clause = "customer.cust_name LIKE '%$search%'";
            break;
        case 'review_id':
            $where_clause = "review.rev_id LIKE '%$search%'";
            break;
        case 'comment':
            $where_clause = "review.comment LIKE '%$search%'";
            break;
        default:
            $where_clause = "1"; // Default to no filtering
    }
} else {
    $where_clause = "1"; // Default to showing all reviews
}

// Query to get reviews based on the search term and category
$query = "SELECT review.rev_id, customer.cust_name, review.rating, review.comment 
          FROM review 
          JOIN customer ON review.cust_id = customer.cust_id 
          WHERE $where_clause";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Management</title>
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
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
        }
        .search-form {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .search-input {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 250px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .search-input:focus {
            border-color: #007BFF;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            outline: none;
        }
        .search-btn {
            padding: 10px 15px;
            background-color: rgb(145, 98, 199);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .search-btn:hover {
            background-color:rgb(149, 0, 179);
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
    <h1>Review Management</h1>

    <!-- Search Form -->
    <form method="POST" action="" class="search-form">
        <select name="category" class="search-select">
            <option value="review_id" <?php if ($category == 'review_id') echo 'selected'; ?>>Review ID</option>
            <option value="customer" <?php if ($category == 'customer') echo 'selected'; ?>>Customer Name</option>
            <option value="rating" <?php if ($category == 'rating') echo 'selected'; ?>>Rating</option>
            <option value="comment" <?php if ($category == 'comment') echo 'selected'; ?>>Comment</option>
        </select>
        <input type="text" name="search" placeholder="Search..." value="<?php echo $search; ?>" class="search-input">
        <button type="submit" class="search-btn">Search</button>
    </form>

    <!-- Logout Button -->
    <a href="logout.php" class="logout">Logout</a>
    <a href="receptionist_dashboard.php" class="home">Home</a>

    <div class="container">
        <h2>Review List</h2>
        <table>
            <tr>
                <th>Review ID</th>
                <th>Customer Name</th>
                <th>Rating</th>
                <th>Comment</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['rev_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['cust_name']); ?></td>
                    <td><?php echo $row['rating']; ?></td>
                    <td><?php echo htmlspecialchars($row['comment']); ?></td>
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
