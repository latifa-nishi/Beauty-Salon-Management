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
$selected_date = '';
$selected_month = '';
$selected_year = '';
$selected_category = '';
$sales_data = [];
$period_text = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_date = isset($_POST['date']) ? $_POST['date'] : '';
    $selected_month = isset($_POST['month']) ? $_POST['month'] : '';
    $selected_year = isset($_POST['year']) ? $_POST['year'] : '';
    $selected_category = isset($_POST['category']) ? $_POST['category'] : '';

    // Modified query to ensure all staff are shown
    $query = "SELECT 
                s.staff_id,
                s.staff_name,
                s.staff_role,
                COALESCE(COUNT(DISTINCT CASE 
                    WHEN ad.status = 'Confirmed' THEN as2.app_id 
                    END), 0) as demand
              FROM staff s
              LEFT JOIN appointment_services as2 ON s.staff_id = as2.staff_id
              LEFT JOIN appointment_details ad ON as2.app_id = ad.app_id";

    // Where clause starts here
    $whereClauses = [];
    
    // Add category filter if selected
    if (!empty($selected_category)) {
        $category = $conn->real_escape_string($selected_category);
        $whereClauses[] = "s.staff_role = '$category'";
    }

    // Add date filters
    if ($selected_date) {
        $whereClauses[] = "DATE(ad.app_date) = '$selected_date'";
        $period_text = "Date: " . date('d M Y', strtotime($selected_date));
    } elseif ($selected_month) {
        $month_parts = explode('-', $selected_month);
        if (count($month_parts) == 2) {
            $year = $month_parts[0];
            $month_num = $month_parts[1];
            $whereClauses[] = "MONTH(ad.app_date) = '$month_num' AND YEAR(ad.app_date) = '$year'";
            $period_text = "Month: " . date('F Y', strtotime($selected_month . '-01'));
        }
    } elseif ($selected_year) {
        $whereClauses[] = "YEAR(ad.app_date) = '$selected_year'";
        $period_text = "Year: " . $selected_year;
    }

    // Add WHERE clause if there are any conditions
    if (!empty($whereClauses)) {
        $query .= " WHERE " . implode(" AND ", $whereClauses);
    }

    $query .= " GROUP BY s.staff_id, s.staff_name, s.staff_role
                ORDER BY demand DESC, s.staff_name ASC";

    // Execute query and fetch results
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $sales_data[] = [
                'staff_id' => $row['staff_id'],
                'staff_name' => $row['staff_name'],
                'staff_role' => $row['staff_role'],
                'demand' => $row['demand']
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Popularity</title>
    <style>
         * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #eee;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .period-text {
            color: #3498db;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: bold;
        }

        input {
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        button {
            padding: 0.8rem 1.5rem;
            background: rgb(145, 98, 199);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s ease;
        }

        button:hover {
            background: rgb(149, 0, 179);
        }

        .sales-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }

        .sales-table th,
        .sales-table td {
            padding: 1rem;
            text-align: left;
            border: 1px solid #ddd;
        }

        .sales-table th {
            background: rgb(145, 98, 199);
            color: white;
        }

        .sales-table tr:nth-child(even) {
            background: #f8f9fa;
        }

        .sales-table tr:hover {
            background: #eef2f7;
        }

        .total-row {
            font-weight: bold;
            background: #e1f0fa !important;
        }

        .total-row td {
            border-top: 2px solid #3498db;
        }

        .amount {
            text-align: right;
        }

        .no-data {
            text-align: center;
            padding: 2rem;
            color: #666;
            font-style: italic;
        }

        .logout-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.5rem 1rem;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s ease;
        }

        .logout-btn:hover {
            background: #c0392b;
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
    <a href="logout.php" class="logout-btn">Logout</a>
    <a href="staff_dashboard.php" class="home">Home</a>
    
    <div class="container">
        <div class="header">
            <h1>Staff Popularity Report</h1>
            <?php if ($period_text): ?>
                <div class="period-text"><?php echo htmlspecialchars($period_text); ?></div>
            <?php endif; ?>
        </div>

        <form method="POST" class="search-form">
            <div class="form-group">
                <label for="category">Category:</label>
                <select name="category" id="category" class="form-control">
                    <option value="">All Categories</option>
                    <option value="Senior Stylist" <?php echo $selected_category === 'Senior Stylist' ? 'selected' : ''; ?>>Senior Stylist</option>
                    <option value="Makeup Artist" <?php echo $selected_category === 'Makeup Artist' ? 'selected' : ''; ?>>Makeup Artist</option>
                    <option value="Hair Specialist" <?php echo $selected_category === 'Hair Specialist' ? 'selected' : ''; ?>>Hair Specialist</option>
                    <option value="Nail Artist" <?php echo $selected_category === 'Nail Artist' ? 'selected' : ''; ?>>Nail Artist</option>
                    <option value="Facial Specialist" <?php echo $selected_category === 'Facial Specialist' ? 'selected' : ''; ?>>Facial Specialist</option>
                    <option value="Massage Therapist" <?php echo $selected_category === 'Massage Therapist' ? 'selected' : ''; ?>>Massage Therapist</option>
                    <option value="Waxing Specialist" <?php echo $selected_category === 'Waxing Specialist' ? 'selected' : ''; ?>>Waxing Specialist</option>
                    <option value="Beauty Therapist" <?php echo $selected_category === 'Beauty Therapist' ? 'selected' : ''; ?>>Beauty Therapist</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="date">Select Date:</label>
                <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($selected_date); ?>">
            </div>
            
            <div class="form-group">
                <label for="month">Select Month:</label>
                <input type="month" id="month" name="month" value="<?php echo htmlspecialchars($selected_month); ?>">
            </div>
            
            <div class="form-group">
                <label for="year">Select Year:</label>
                <input type="number" id="year" name="year" min="2000" max="2100" placeholder="YYYY" 
                       value="<?php echo htmlspecialchars($selected_year); ?>">
            </div>
            
            <div class="form-group">
                <label>&nbsp;</label>
                <button type="submit">Generate Report</button>
            </div>
        </form>

        <table class="sales-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>No. of Appointments</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($sales_data)): ?>
                    <?php foreach ($sales_data as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['staff_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['staff_role']); ?></td>
                            <td class="amount"><?php echo htmlspecialchars($row['demand']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="no-data">No data found for the selected criteria</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    // Clear other fields when one is selected
    document.getElementById('date').addEventListener('change', function() {
        if(this.value) {
            document.getElementById('month').value = '';
            document.getElementById('year').value = '';
        }
    });

    document.getElementById('month').addEventListener('change', function() {
        if(this.value) {
            document.getElementById('date').value = '';
            document.getElementById('year').value = '';
        }
    });

    document.getElementById('year').addEventListener('change', function() {
        if(this.value) {
            document.getElementById('date').value = '';
            document.getElementById('month').value = '';
        }
    });
    </script>
</body>
</html>