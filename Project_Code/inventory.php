<?php
session_start();

// Database configuration
$config = [
    'host' => 'localhost',
    'dbname' => 'beauty_parlor',
    'username' => 'root',
    'password' => ''
];

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']}", 
        $config['username'], 
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Function to get inventory status with product name filter
function getInventoryStatus($pdo, $product_name = '') {
    $query = "SELECT 
                i.product_id,
                i.product_name,
                i.product_category,
                i.quantity as current_stock,
                i.price,
                SUM(COALESCE(is2.quantity_used, 0)) as total_used,
                i.quantity - SUM(COALESCE(is2.quantity_used, 0)) as remaining_stock
              FROM inventory i
              LEFT JOIN inventory_services is2 ON i.product_id = is2.product_id";
    
    if ($product_name) {
        $query .= " WHERE i.product_name LIKE :product_name";
    }
    
    $query .= " GROUP BY i.product_id";
    
    $stmt = $pdo->prepare($query);
    if ($product_name) {
        $stmt->execute([':product_name' => '%' . $product_name . '%']);
    } else {
        $stmt->execute();
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get usage history with product name filter
function getUsageHistory($pdo, $product_name = '') {
    $query = "SELECT 
                is2.appointment_date,
                i.product_name,
                i.product_category,
                s.sname as service_name,
                is2.quantity_used,
                c.cust_name,
                st.staff_name
              FROM inventory_services is2
              JOIN inventory i ON is2.product_id = i.product_id
              JOIN services s ON is2.sid = s.sid
              JOIN appointment a ON is2.app_id = a.app_id
              JOIN customer c ON a.cust_id = c.cust_id
              JOIN appointment_services aps ON is2.app_id = aps.app_id AND is2.sid = aps.sid
              JOIN staff st ON aps.staff_id = st.staff_id";
    
    if ($product_name) {
        $query .= " WHERE i.product_name LIKE :product_name";
    }
    
    $query .= " ORDER BY is2.appointment_date DESC";
    
    $stmt = $pdo->prepare($query);
    if ($product_name) {
        $stmt->execute([':product_name' => '%' . $product_name . '%']);
    } else {
        $stmt->execute();
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get all product names
function getProductNames($pdo) {
    $query = "SELECT DISTINCT product_name FROM inventory ORDER BY product_name";
    return $pdo->query($query)->fetchAll(PDO::FETCH_COLUMN);
}

// Function to update inventory
function updateInventory($pdo, $product_id, $new_quantity) {
    $query = "UPDATE inventory SET quantity = :quantity WHERE product_id = :product_id";
    $stmt = $pdo->prepare($query);
    return $stmt->execute([ 
        ':quantity' => $new_quantity,
        ':product_id' => $product_id
    ]);
}

// Handle form submission for updating inventory
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_inventory'])) {
    updateInventory($pdo, $_POST['product_id'], $_POST['new_quantity']);
}

// Get product name filter from query string
$product_name_filter = $_GET['product_name'] ?? '';

// Get data
$product_names = getProductNames($pdo);
$inventory_status = getInventoryStatus($pdo, $product_name_filter);
$usage_history = getUsageHistory($pdo, $product_name_filter);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
    <div class="container mt-5">
        <h1 class="mb-4">Inventory Management System</h1>
        <a href="logout.php" class="logout">Logout</a>
        <a href="admin_dashboard.php" class="home">Home</a>
        <!-- Product Name Dropdown -->
        <div class="mb-4">
            <form method="get" class="row g-3">
                <div class="col-auto">
                    <select name="product_name" class="form-select">
                        <option value="">Select Product</option>
                        <?php foreach ($product_names as $product_name): ?>
                            <option value="<?php echo htmlspecialchars($product_name); ?>"
                                    <?php echo $product_name_filter === $product_name ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($product_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
                <?php if ($product_name_filter): ?>
                    <div class="col-auto">
                        <a href="?" class="btn btn-secondary">Clear Filter</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Current Inventory Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-title h5 mb-0">Current Inventory Status</h2>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Initial Stock</th>
                            <th>Used</th>
                            <th>Remaining</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory_status as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['product_category']); ?></td>
                                <td><?php echo htmlspecialchars($item['current_stock']); ?></td>
                                <td><?php echo htmlspecialchars($item['total_used'] ?? 0); ?></td>
                                <td><?php echo htmlspecialchars($item['remaining_stock']); ?></td>
                                <td>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <input type="number" name="new_quantity" class="form-control form-control-sm d-inline" style="width: 80px;">
                                        <button type="submit" name="update_inventory" class="btn btn-primary btn-sm">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Usage History -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title h5 mb-0">Usage History</h2>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Service</th>
                            <th>Quantity Used</th>
                            <th>Customer</th>
                            <th>Staff</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usage_history as $usage): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usage['appointment_date']); ?></td>
                                <td><?php echo htmlspecialchars($usage['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($usage['product_category']); ?></td>
                                <td><?php echo htmlspecialchars($usage['service_name']); ?></td>
                                <td><?php echo htmlspecialchars($usage['quantity_used']); ?></td>
                                <td><?php echo htmlspecialchars($usage['cust_name']); ?></td>
                                <td><?php echo htmlspecialchars($usage['staff_name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
