<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "beauty_parlor";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$search = "";
$category = "";

// Search functionality
if (isset($_POST['search_term']) && isset($_POST['search_category'])) {
    $search = mysqli_real_escape_string($conn, $_POST['search_term']);
    $category = mysqli_real_escape_string($conn, $_POST['search_category']);

    $allowed_categories = ['product_id', 'product_name', 'product_category', 'quantity', 'price', 'supplier_name', 'supplier_contact'];
    if (in_array($category, $allowed_categories)) {
        $query = "SELECT * FROM inventory WHERE $category LIKE '%$search%'";
    } else {
        $query = "SELECT * FROM inventory";
    }
} else {
    $query = "SELECT * FROM inventory";
}

$result = $conn->query($query);

// Insert new inventory item
if (isset($_POST['add_inventory'])) {
    try {
        $conn->begin_transaction();
        
        // Generate product ID
        $prod_query = "SELECT MAX(CAST(SUBSTRING(product_id, 2) AS UNSIGNED)) as max_id FROM inventory";
        $result = $conn->query($prod_query);
        $row = $result->fetch_assoc();
        $next_id = $row['max_id'] + 1;
        $product_id = 'P' . str_pad($next_id, 3, '0', STR_PAD_LEFT);
        
        // Get form data
        $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
        $product_category = mysqli_real_escape_string($conn, $_POST['product_category']);
        $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
        $price = mysqli_real_escape_string($conn, $_POST['price']);
        $supplier_name = mysqli_real_escape_string($conn, $_POST['supplier_name']);
        $supplier_contact = mysqli_real_escape_string($conn, $_POST['supplier_contact']);

        // Insert inventory item
        $stmt = $conn->prepare("INSERT INTO inventory (product_id, product_name, product_category, quantity, price, supplier_name, supplier_contact) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiiss", $product_id, $product_name, $product_category, $quantity, $price, $supplier_name, $supplier_contact);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        echo "<div class='success-message'>Product added successfully! ID: $product_id</div>";
        
        // Redirect to avoid form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
        
    } catch (Exception $e) {
        // Rollback in case of error
        $conn->rollback();
        echo "<div class='error-message'>Error adding product: " . $e->getMessage() . "</div>";
    }
}

// Delete inventory
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM inventory WHERE product_id = ?";
    
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("s", $delete_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Inventory item deleted successfully');</script>";
    } else {
        echo "<script>alert('Error deleting inventory item');</script>";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <style>
        /* Keeping existing styles */
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

        /* Add styles for success and error messages */
        .success-message, .error-message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            text-align: center;
        }

        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }

        .error-message {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }

        /* Existing form styles remain the same */
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
    <h1>Inventory Management</h1>

    <!-- Search Form -->
    <form method="POST" action="" class="search-form">
        <select name="search_category" class="search-select">
            <option value="">-- Select Category --</option>
            <option value="product_id">Product ID</option>
            <option value="product_name">Product Name</option>
            <option value="product_category">Product Category</option>
            <option value="quantity">Quantity</option>
            <option value="price">Price</option>
            <option value="supplier_name">Supplier Name</option>
            <option value="supplier_contact">Supplier Contact</option>
        </select>
        <input type="text" name="search_term" placeholder="Enter search term..." class="search-input">
        <button type="submit" class="search-btn">Search</button>
    </form>

    <a href="logout.php" class="logout">Logout</a>
    <a href="receptionist_dashboard.php" class="home">Home</a>

    <!-- Insert Form (removed product_id field) -->
    <div class="container">
        <h2>Add New Inventory Item</h2>
        <form method="POST" action="" class="insert-form">
            <input type="text" name="product_name" placeholder="Product Name" class="input-field" required>
            <input type="text" name="product_category" placeholder="Product Category" class="input-field" required>
            <input type="number" name="quantity" placeholder="Quantity" class="input-field" required>
            <input type="number" step="0.01" name="price" placeholder="Price" class="input-field" required>
            <input type="text" name="supplier_name" placeholder="Supplier Name" class="input-field" required>
            <input type="text" name="supplier_contact" placeholder="Supplier Contact" class="input-field" required>
            <button type="submit" name="add_inventory" class="insert-btn">Add Item</button>
        </form>
    </div>

    <!-- Inventory List -->
    <div class="container">
        <h2>Inventory List</h2>
        <table border="1" align="center">
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Product Category</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Supplier Name</th>
                <th>Supplier Contact</th>
                <th>Action</th>
            </tr>
            <?php 
            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()): 
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['product_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['product_category']); ?></td>
                    <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($row['price']); ?></td>
                    <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['supplier_contact']); ?></td>
                    <td>
                        <a href="?delete_id=<?php echo $row['product_id']; ?>" class="delete-btn" 
                           onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <?php $conn->close(); ?>
</body>
</html>