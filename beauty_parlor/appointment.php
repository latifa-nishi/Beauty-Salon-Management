<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "beauty_parlor");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get search parameters
$date = isset($_GET['date']) && !empty($_GET['date']) ? $_GET['date'] : null;
$service_category = isset($_GET['service_category']) ? $_GET['service_category'] : '';
$search_term = isset($_GET['search_term']) ? $_GET['search_term'] : '';
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'DESC';

// Base query (ordering by app_id to maintain input order)
$query = "
    SELECT DISTINCT
        a.app_id,
        c.cust_name,
        c.cust_phone,
        s.sname as service_name,
        s.sduration as service_duration,
        st.staff_name,
        st.staff_role,
        ad.status,
        ad.app_date,
        TIME_FORMAT(s.sduration, '%H:%i') as formatted_duration
    FROM appointment a
    JOIN customer c ON a.cust_id = c.cust_id
    JOIN appointment_services aps ON a.app_id = aps.app_id
    JOIN services s ON aps.sid = s.sid
    JOIN staff st ON aps.staff_id = st.staff_id
    JOIN appointment_details ad ON a.app_id = ad.app_id
    WHERE 1=1";

$params = [];
$param_types = "";

if ($date) {
    $query .= " AND DATE(ad.app_date) = ?";
    $params[] = $date;
    $param_types .= "s";
}

if ($service_category) {
    $query .= " AND s.sname = ?";
    $params[] = $service_category;
    $param_types .= "s";
}

if ($search_term) {
    $query .= " AND (c.cust_name LIKE ? OR c.cust_phone LIKE ? OR st.staff_name LIKE ?)";
    $search_param = "%$search_term%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "sss";
}

// Modify order to use app_id for sorting
$query .= " ORDER BY a.app_id " . ($sort_order === 'ASC' ? 'ASC' : 'DESC');


// Get all services for dropdown
$services_query = "SELECT DISTINCT sname FROM services ORDER BY sname";
$services_result = $conn->query($services_query);
$services = [];
while ($row = $services_result->fetch_assoc()) {
    $services[] = $row['sname'];
}

// Prepare and execute the main query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Store results in a structured array
$appointments = [];
while ($row = $result->fetch_assoc()) {
    $app_id = $row['app_id'];
    if (!isset($appointments[$app_id])) {
        $appointments[$app_id] = [
            'customer_name' => $row['cust_name'],
            'customer_phone' => $row['cust_phone'],
            'status' => $row['status'],
            'app_date' => $row['app_date'],
            'services' => [],
            'total_duration' => 0
        ];
    }
    
    // Add service details
    $appointments[$app_id]['services'][] = [
        'service_name' => $row['service_name'],
        'staff_name' => $row['staff_name'],
        'staff_role' => $row['staff_role'],
        'duration' => $row['formatted_duration']
    ];
    
    // Add duration
    $duration_parts = explode(':', $row['service_duration']);
    $minutes = $duration_parts[0] * 60 + $duration_parts[1];
    $appointments[$app_id]['total_duration'] += $minutes;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beauty Parlor Appointments</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        .sort-arrow {
            display: inline-block;
            width: 0;
            height: 0;
            margin-left: 5px;
            vertical-align: middle;
        }
        .sort-arrow.up {
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-bottom: 4px solid currentColor;
        }
        .sort-arrow.down {
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-top: 4px solid currentColor;
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
<body class="bg-gray-100 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Beauty Parlor Appointments</h1>
            <a href="admin_dashboard.php" class="home">Home</a>
            <a href="logout.php" class="logout">Logout</a>
            <!-- Search Form -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                        <input type="date" name="date" value="<?php echo $date; ?>" 
                               class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Service Category</label>
                        <select name="service_category" class="w-full border rounded px-3 py-2">
                            <option value="">All Services</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?php echo htmlspecialchars($service); ?>"
                                    <?php echo $service_category === $service ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($service); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search_term" value="<?php echo htmlspecialchars($search_term); ?>" 
                               placeholder="Search name or phone" 
                               class="w-full border rounded px-3 py-2">
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" 
                                class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Search
                        </button>
                    </div>
                    <input type="hidden" name="sort" value="<?php echo $sort_order; ?>">
                </form>
            </div>

            <!-- Sort Controls -->
            <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                <div class="flex justify-between items-center">
                    <div class="text-gray-600">
                        Sort by App ID:
                    </div>
                    <div>
                        <?php
                        $params = $_GET;
                        
                        $params['sort'] = 'ASC';
                        $asc_url = '?' . http_build_query($params);
                        
                        $params['sort'] = 'DESC';
                        $desc_url = '?' . http_build_query($params);
                        ?>
                        
                        <a href="<?php echo $asc_url; ?>" 
                           class="inline-flex items-center px-3 py-2 rounded <?php echo $sort_order === 'ASC' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100'; ?>">
                            Oldest First
                            <?php if ($sort_order === 'ASC'): ?>
                                <span class="sort-arrow up ml-1"></span>
                            <?php endif; ?>
                        </a>
                        
                        <a href="<?php echo $desc_url; ?>" 
                           class="inline-flex items-center px-3 py-2 rounded ml-2 <?php echo $sort_order === 'DESC' ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-gray-100'; ?>">
                            Newest First
                            <?php if ($sort_order === 'DESC'): ?>
                                <span class="sort-arrow down ml-1"></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Results Count -->
            <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                <div class="text-gray-600">
                    Showing <?php echo count($appointments); ?> appointment(s)
                    <?php if ($date): ?>
                        for <?php echo date('F j, Y', strtotime($date)); ?>
                    <?php endif; ?>
                    <?php if ($service_category): ?>
                        in <?php echo htmlspecialchars($service_category); ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (empty($appointments)): ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4">
                    No appointments found for the selected criteria.
                </div>
            <?php else: ?>
                <?php foreach ($appointments as $app_id => $appointment): ?>
                    <div class="bg-white rounded-lg shadow-md mb-6 p-6">
                        <div class="border-b pb-4 mb-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h2 class="text-xl font-semibold text-gray-800">
                                        <?php echo htmlspecialchars($appointment['customer_name']); ?>
                                        <span class="text-sm text-gray-500 ml-2">
                                            (<?php echo htmlspecialchars($appointment['customer_phone']); ?>)
                                        </span>
                                    </h2>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Date: <?php echo date('F j, Y', strtotime($appointment['app_date'])); ?>
                                    </p>
                                </div>
                                <div class="mt-2">
                                    <span class="px-3 py-1 rounded-full text-sm 
                                        <?php 
                                            switch($appointment['status']) {
                                                case 'Confirmed':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'Pending':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'Cancelled':
                                                    echo 'bg-red-100 text-red-800';
                                                    break;
                                                default:
                                                    echo 'bg-gray-100 text-gray-800';
                                            }
                                        ?>">
                                        <?php echo $appointment['status']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <?php foreach ($appointment['services'] as $service): ?>
                                <div class="flex items-center justify-between bg-gray-50 p-4 rounded">
                                    <div>
                                        <h3 class="font-medium text-gray-800">
                                            <?php echo htmlspecialchars($service['service_name']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            Staff: <?php echo htmlspecialchars($service['staff_name']); ?> 
                                            (<?php echo htmlspecialchars($service['staff_role']); ?>)
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-600">
                                            Duration: <?php echo $service['duration']; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="mt-4 pt-4 border-t">
                                <p class="text-right text-lg font-medium text-gray-800">
                                    Total Duration: 
                                    <?php 
                                    $hours = floor($appointment['total_duration'] / 60);
                                    $minutes = $appointment['total_duration'] % 60;
                                    echo sprintf("%02d:%02d", $hours, $minutes);
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
