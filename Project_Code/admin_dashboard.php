<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e8f1f9; /* Subtle light blue background */
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff; /* White background */
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #000000; /* Black for the welcome text */
        }
        .options {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .option {
            width: 200px;
            padding: 20px;
            text-align: center;
            background-color: #5a9bd8; /* Slightly darker blue */
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .option:hover {
            background-color: #4178a6; /* A darker blue on hover */
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Enhanced shadow on hover */
        }
        .logout {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 15px;
            background-color: #f44336; /* Red color for logout button */
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        }
        .logout:hover {
            background-color: #d32f2f; /* Darker red on hover */
            transform: translateY(-3px);
        }
        @media (max-width: 768px) {
            .option {
                width: 100%; /* Full width for smaller screens */
            }
        }
    </style>
</head>
<body>
    <!-- Logout Button -->
    <a href="logout.php" class="logout">Logout</a>

    <div class="container">
        <h1>Welcome to Beauty Parlor Management, ADMIN</h1>
        <div class="options">
            <a href="admin.php" class="option">Admin</a>
            <a href="customer.php" class="option">Customers</a>
            <a href="staff.php" class="option">Staff</a>
            <a href="appointment.php" class="option">Appointment List</a>
            <a href="appointment_approve.php" class="option">Appointment Confirmation</a>
            <a href="staff_schedule.php" class="option">Staff Schedule</a>
            <a href="services.php" class="option">Services</a>
            <a href="sales_report.php" class="option">Sales Report</a>
            <a href="staff_popularity.php" class="option">Staff FanBase</a>
            <a href="inventory.php" class="option">Inventory Management</a>
            <a href="inventory_update.php" class="option">Inventory Updation</a>
            <a href="review.php" class="option">Review</a>
        </div>
    </div>
</body>
</html>
