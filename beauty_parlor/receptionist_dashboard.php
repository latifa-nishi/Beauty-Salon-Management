<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f7f1; /* Subtle cream background */
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
            color: #333333; /* Dark gray for the welcome text */
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
            background-color:rgb(171, 101, 232); /* Light orange for option boxes */
            color:rgb(255, 255, 255); /* Medium gray text */
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .option:hover {
            background-color:rgb(191, 89, 205); /* Darker orange on hover */
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
        <h1>Welcome to Beauty Parlor Management, Receptionist</h1>
        <div class="options">
            <a href="re_customer.php" class="option">Customers</a>
            <a href="re_staff.php" class="option">Staff</a>
            <a href="re_appointment.php" class="option">Appointment List</a>
            <a href="re_appointment_approve.php" class="option">Appointment Confirmation</a>
            <a href="re_staff_schedule.php" class="option">Staff Schedule</a>
            <a href="re_services.php" class="option">Services</a>
            <a href="re_staff_popularity.php" class="option">Staff FanBase</a>
            <a href="re_inventory.php" class="option">Inventory Management</a>
            <a href="re_inventory_updation.php" class="option">Inventory Updation</a>
            <a href="re_review.php" class="option">Review</a>
        </div>
    </div>
</body>
</html>
