<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
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

// Retrieve available services
$services_query = "SELECT * FROM services";
$services_result = $conn->query($services_query);

// Retrieve available staff members
$staff_query = "SELECT * FROM staff";
$staff_result = $conn->query($staff_query);

// Handling the form submission (booking the appointment)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_id = $_POST['service_id'];
    $staff_id = $_POST['staff_id'];
    $appointment_date = $_POST['appointment_date'];
    $customer_id = $_SESSION['user_id'];

    // Generate appointment ID
    $appointment_id = 'AP' . strtoupper(uniqid());

    // Insert appointment record
    $sql_appointment = "INSERT INTO appointment (app_id, app_date, cust_id) 
                        VALUES ('$appointment_id', '$appointment_date', '$customer_id')";

    if ($conn->query($sql_appointment) === TRUE) {
        // Insert appointment services
        $sql_appointment_services = "INSERT INTO appointment_services (app_id, sid, staff_id) 
                                     VALUES ('$appointment_id', '$service_id', '$staff_id')";

        if ($conn->query($sql_appointment_services) === TRUE) {
            header("Location: staff_dashboard.php");
            exit();
        } else {
            echo "Error: " . $sql_appointment_services . "<br>" . $conn->error;
        }
    } else {
        echo "Error: " . $sql_appointment . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Connection</title>
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
        h1 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-size: 16px;
        }
        select, input[type="date"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            font-size: 16px;
        }
        .submit-button {
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            display: block;
            width: 100%;
        }
        .submit-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Book a Service</h1>
    <form method="POST" action="service_connection.php">
        <div class="form-group">
            <label for="service_id">Select Service:</label>
            <select name="service_id" id="service_id" required>
                <option value="">-- Select a Service --</option>
                <?php while ($service = $services_result->fetch_assoc()): ?>
                    <option value="<?php echo $service['sid']; ?>"><?php echo $service['sname']; ?> - $<?php echo $service['sprice']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="staff_id">Select Staff Member:</label>
            <select name="staff_id" id="staff_id" required>
                <option value="">-- Select Staff --</option>
                <?php while ($staff = $staff_result->fetch_assoc()): ?>
                    <option value="<?php echo $staff['staff_id']; ?>"><?php echo $staff['staff_name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="appointment_date">Select Appointment Date:</label>
            <input type="date" name="appointment_date" id="appointment_date" required>
        </div>

        <button type="submit" class="submit-button">Book Appointment</button>
    </form>
</div>

</body>
</html>

<?php
// Close connection
$conn->close();
?>
