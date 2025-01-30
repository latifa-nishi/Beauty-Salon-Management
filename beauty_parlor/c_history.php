<?php include 'includes/header.php'; ?>
<?php include 'includes/db_connect.php'; ?>

<div class="content">
    <main>
        <h1>Appointment History</h1>
        <form method="GET" action="">
            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" required>
            <button type="submit">View History</button>
        </form>
        <ul>
            <?php
            if (isset($_GET['phone'])) {
                $phone = $_GET['phone'];
                $sql = "SELECT cust_id FROM customer WHERE cust_phone='$phone'";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $cust_id = $row['cust_id'];

                    $sql = "SELECT a.app_id, a.app_date, ad.total_bill, ad.status, s.sname, s.sprice, st.staff_name 
                            FROM appointment a
                            JOIN appointment_details ad ON a.app_id = ad.app_id
                            JOIN appointment_services aps ON a.app_id = aps.app_id
                            JOIN services s ON aps.sid = s.sid
                            JOIN staff st ON aps.staff_id = st.staff_id
                            WHERE a.cust_id = '$cust_id'";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<li>
                                    <strong>Appointment ID:</strong> {$row['app_id']}<br>
                                    <strong>Date:</strong> {$row['app_date']}<br>
                                    <strong>Service:</strong> {$row['sname']} ({$row['sprice']} BDT)<br>
                                    <strong>Staff:</strong> {$row['staff_name']}<br>
                                    <strong>Total Bill:</strong> {$row['total_bill']} BDT<br>
                                    <strong>Status:</strong> {$row['status']}<br>
                                    <form method='POST' action='history.php'>
                                        <input type='hidden' name='app_id' value='{$row['app_id']}'>
                                        <button type='submit' name='cancel'>Cancel Appointment</button>
                                    </form>
                                  </li>";
                        }
                    } else {
                        echo "<li>No appointment history found</li>";
                    }
                } else {
                    echo "<li>No customer found with this phone number</li>";
                }
            }

            if (isset($_POST['cancel'])) {
                $app_id = $_POST['app_id'];
                $sql = "DELETE FROM appointment WHERE app_id='$app_id'";
                if ($conn->query($sql) === TRUE) {
                    echo "<p>Appointment cancelled successfully!</p>";
                } else {
                    echo "<p>Error cancelling appointment: " . $conn->error . "</p>";
                }
            }
            ?>
        </ul>
    </main>
</div>
<?php include 'includes/footer.php'; ?>