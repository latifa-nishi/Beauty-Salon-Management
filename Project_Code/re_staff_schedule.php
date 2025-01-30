<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "beauty_parlor");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get selected date and search query
$selectedDate = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');
$searchQuery = isset($_POST['search']) ? trim($_POST['search']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Schedules</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .schedule-card {
            border-left: 4px solid #007BFF;
            margin-bottom: 10px;
        }
        .service-duration {
            font-size: 0.9em;
            color: #6c757d;
        }
        .search-btn {
            padding: 10px 150px;
            background-color: rgb(145, 98, 199);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-btn:hover {
            background-color: rgb(149, 0, 179);
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
        .search-form {
            margin-bottom: 20px;
        }
        .search-form input {
            border-radius: 5px;
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
.search-btn {
    padding: 10px 150px;
    background-color: rgb(145, 98, 199);
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s;
    margin-top: 25px; /* Adjust this value as needed */
    align-self: center; /* Ensure alignment with other fields in flex containers */
}

.search-btn:hover {
    background-color: rgb(149, 0, 179);
}


    </style>
</head>
<body>

<a href="logout.php" class="logout">Logout</a>
<a href="receptionist_dashboard.php" class="home">Home</a>
<div class="container mt-4">
    <h2 class="mb-4">Staff Schedules</h2>
    
    <!-- Date Selection and Search Form -->
    <form method="POST" class="row search-form">
        <div class="col-md-4">
            <label for="date" class="form-label">Select Date:</label>
            <input type="date" class="form-control" id="date" name="date" value="<?php echo $selectedDate; ?>">
        </div>
        <div class="col-md-4">
            <label for="search" class="form-label">Search Staff:</label>
            <input type="text" class="form-control" id="search" name="search" 
                   placeholder="Enter Staff ID, Name, or Role" 
                   value="<?php echo htmlspecialchars($searchQuery); ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="search-btn">Search</button>
        </div>
    </form>

    <?php
    // Modified query to include search by staff ID, name, and role
    $query = "
        SELECT 
            s.staff_id,
            s.staff_name,
            s.staff_role,
            a.app_id,
            a.app_date,
            c.cust_name,
            sv.sname,
            sv.sduration
        FROM staff s
        LEFT JOIN appointment_services as_srv ON s.staff_id = as_srv.staff_id
        LEFT JOIN appointment a ON as_srv.app_id = a.app_id
        LEFT JOIN services sv ON as_srv.sid = sv.sid
        LEFT JOIN customer c ON a.cust_id = c.cust_id
        LEFT JOIN appointment_details ad ON a.app_id = ad.app_id
        WHERE a.app_date = ? AND ad.status = 'Confirmed'";

    if (!empty($searchQuery)) {
        $query .= " AND (s.staff_id LIKE ? OR s.staff_name LIKE ? OR s.staff_role LIKE ?)";
    }

    $query .= " ORDER BY s.staff_name, sv.sname";

    $stmt = $conn->prepare($query);

    if (!empty($searchQuery)) {
        $searchParam = "%$searchQuery%";
        $stmt->bind_param("ssss", $selectedDate, $searchParam, $searchParam, $searchParam);
    } else {
        $stmt->bind_param("s", $selectedDate);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Group results by staff
    $staffSchedules = [];
    while ($row = $result->fetch_assoc()) {
        if ($row['staff_id']) {
            $staffSchedules[$row['staff_id']]['info'] = [
                'name' => $row['staff_name'],
                'role' => $row['staff_role']
            ];
            if ($row['app_id']) {
                $staffSchedules[$row['staff_id']]['appointments'][] = [
                    'customer' => $row['cust_name'],
                    'service' => $row['sname'],
                    'duration' => $row['sduration']
                ];
            }
        }
    }

    // Display schedules
    if (empty($staffSchedules)) {
        echo "<div class='alert alert-info'>No results found for the selected criteria.</div>";
    } else {
        foreach ($staffSchedules as $staffId => $schedule) {
            ?>
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><?php echo htmlspecialchars($schedule['info']['name']); ?></h5>
                    <small class="text-muted"><?php echo htmlspecialchars($schedule['info']['role']); ?></small>
                </div>
                <div class="card-body">
                    <?php
                    if (isset($schedule['appointments'])) {
                        foreach ($schedule['appointments'] as $appointment) {
                            ?>
                            <div class="schedule-card p-3 bg-light rounded">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Customer:</strong> 
                                        <?php echo htmlspecialchars($appointment['customer']); ?>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Service:</strong> 
                                        <?php echo htmlspecialchars($appointment['service']); ?>
                                    </div>
                                    <div class="col-md-4 service-duration">
                                        <strong>Duration:</strong> 
                                        <?php echo htmlspecialchars($appointment['duration']); ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<p class='text-muted'>No confirmed appointments scheduled</p>";
                    }
                    ?>
                </div>
            </div>
            <?php
        }
    }
    ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
