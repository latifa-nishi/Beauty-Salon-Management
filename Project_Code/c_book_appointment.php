<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
include 'includes/db_connect.php';
?>

<main>
    <h1>Book an Appointment</h1>
    <form method="POST" action="book_appointment.php">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ''; ?>" required>
        
        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone" value="<?php echo isset($_SESSION['user_phone']) ? $_SESSION['user_phone'] : ''; ?>" required>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo isset($_SESSION['user_email']) ? $_SESSION['user_email'] : ''; ?>" required>
        
        <label for="services">Services:</label>
        <select id="services" name="services[]" multiple required>
            <?php
            $sql = "SELECT * FROM services";
            $result = $conn->query($sql);

            while($row = $result->fetch_assoc()) {
                echo "<option value='{$row['sid']}'>{$row['sname']} - {$row['sprice']} BDT</option>";
            }
            ?>
        </select>
        
        <label for="staff">Preferred Staff (optional):</label>
        <select id="staff" name="staff">
            <option value="">No Preference</option>
            <?php
            $sql = "SELECT * FROM staff";
            $result = $conn->query($sql);

            while($row = $result->fetch_assoc()) {
                echo "<option value='{$row['staff_id']}'>{$row['staff_name']} - {$row['staff_role']}</option>";
            }
            ?>
        </select>
        
        <label for="app_date">Appointment Date:</label>
        <input type="date" id="app_date" name="app_date" required>
        
        <label for="pay_method">Payment Method:</label>
        <select id="pay_method" name="pay_method" required>
            <option value="Cash">Cash</option>
            <option value="bKash">bKash</option>
            <option value="Credit Card">Credit Card</option>
            <option value="Nagad">Nagad</option>
            <option value="Rocket">Rocket</option>
        </select>
        
        <button type="submit">Book Appointment</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $services = $_POST['services'];
        $staff = $_POST['staff'];
        $app_date = $_POST['app_date'];
        $pay_method = $_POST['pay_method'];

        // Check if the appointment date is in the past
        $current_date = date('Y-m-d');
        if ($app_date < $current_date) {
            echo "<p class='error-message'>You cannot book an appointment for a past date. Please choose a future date.</p>";
            exit();
        }

        // Check if customer already exists
        $sql = "SELECT cust_id FROM customer WHERE cust_phone='$phone' AND cust_mail='$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $cust_id = $row['cust_id'];
        } else {
            // Insert new customer
            $cust_id = uniqid('C');
            $sql = "INSERT INTO customer (cust_id, cust_name, cust_phone, cust_mail) VALUES ('$cust_id', '$name', '$phone', '$email')";
            $conn->query($sql);
        }

        // Check if staff has less than 3 appointments on the selected date
        if ($staff) {
            $sql = "SELECT COUNT(*) as count FROM appointment_staff WHERE staff_id='$staff' AND app_id IN (SELECT app_id FROM appointment WHERE app_date='$app_date')";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();

            if ($row['count'] >= 3) {
                echo "<p class='error-message'>Selected staff is fully booked on this date. Please choose another staff or date.</p>";
                exit();
            }
        } else {
            // Assign a free staff member
            $sql = "SELECT staff_id FROM staff WHERE staff_id NOT IN (SELECT staff_id FROM appointment_staff WHERE app_id IN (SELECT app_id FROM appointment WHERE app_date='$app_date')) LIMIT 1";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $staff = $row['staff_id'];
            } else {
                echo "<p class='error-message'>No staff available on this date. Please choose another date.</p>";
                exit();
            }
        }

        // Insert appointment
        $app_id = uniqid('AP');
        $sql = "INSERT INTO appointment (app_id, app_date, cust_id) VALUES ('$app_id', '$app_date', '$cust_id')";
        $conn->query($sql);

        // Insert appointment details
        $pay_id = uniqid('P');
        $total_bill = 0;
        foreach ($services as $service) {
            $sql = "SELECT sprice FROM services WHERE sid='$service'";
            $result = $conn->query($sql);
            if ($row = $result->fetch_assoc()) {
                $total_bill += $row['sprice'];
            }
            $sql = "INSERT INTO appointment_services (app_id, sid, staff_id) VALUES ('$app_id', '$service', '$staff')";
            $conn->query($sql);
        }
        $sql = "INSERT INTO appointment_details (pay_id, app_id, cust_id, pay_date, pay_method, total_bill, status, app_date) VALUES ('$pay_id', '$app_id', '$cust_id', NOW(), '$pay_method', '$total_bill', 'Pending', '$app_date')";
        $conn->query($sql);

        // Insert appointment staff
        $sql = "INSERT INTO appointment_staff (app_id, staff_id) VALUES ('$app_id', '$staff')";
        $conn->query($sql);

        echo "<p class='success-message'>Appointment has been booked successfully! Total Amount: {$total_bill} BDT</p>";
    }
    ?>
</main>

<?php include 'includes/footer.php'; ?>

<script>
    // Disable past dates in the date picker
    document.getElementById('app_date').setAttribute('min', new Date().toISOString().split('T')[0]);
</script>