<?php
session_start();

// Check if user is logged in and is a staff member
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'staff') {
    header("Location: staff.php");
    exit();
}

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

// Retrieve staff details
$staff_id = $_SESSION['user_id'];
$sql = "SELECT * FROM staff WHERE staff_id='$staff_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $staff = $result->fetch_assoc();
} else {
    echo "<p>Error: Staff details not found.</p>";
    exit();
}

// Retrieve upcoming appointments for the logged-in staff
$appointments_query = "SELECT appointment.app_id, appointment.app_date, customer.cust_id, customer.cust_name, services.sname 
                       FROM appointment 
                       JOIN appointment_services ON appointment.app_id = appointment_services.app_id
                       JOIN services ON appointment_services.sid = services.sid
                       JOIN customer ON appointment.cust_id = customer.cust_id
                       WHERE appointment_services.staff_id = '$staff_id' 
                       AND appointment.app_date >= CURDATE()
                       ORDER BY appointment.app_date ASC";
$appointments_result = $conn->query($appointments_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            text-align: center;
            color: #333;
        }
        p {
            font-size: 16px;
            line-height: 1.6;
        }
        .logout-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .logout-button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .appointment-table {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($staff['staff_name']); ?>!</h1>
    <p><strong>Role:</strong> <?php echo htmlspecialchars($staff['staff_role']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($staff['staff_mail']); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($staff['staff_phone']); ?></p>
    <p><strong>Location:</strong> <?php echo htmlspecialchars($staff['staff_loc']); ?></p>

    <h2>Your Upcoming Appointments</h2>
    
    <?php if ($appointments_result->num_rows > 0): ?>
        <table class="appointment-table">
            <tr>
                <th>Appointment ID</th>
                <th>Customer Name</th>
                <th>Service</th>
                <th>Appointment Date</th>
            </tr>
            <?php while ($appointment = $appointments_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $appointment['app_id']; ?></td>
                    <td><?php echo $appointment['cust_name']; ?></td>
                    <td><?php echo $appointment['sname']; ?></td>
                    <td><?php echo $appointment['app_date']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No upcoming appointments found.</p>
    <?php endif; ?>

    <a href="logout.php" class="logout-button">Logout</a>
</div>

<?php
// Close connection
$conn->close();
?>

</body>
</html>
