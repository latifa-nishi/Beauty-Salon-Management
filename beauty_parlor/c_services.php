<?php
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

// Fetch services from the database
$sql = "SELECT * FROM services";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Add a CSS file for better styling -->
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="content">
        <div class="container">
            <h1>Our Services</h1>
            <div class="search-bar">
                <input type="text" placeholder="Search services..." id="search" onkeyup="filterServices()">
            </div>
            <div class="filter-bar">
                <button class="filter-btn" onclick="filterServices('all')">All</button>
                <button class="filter-btn" onclick="filterServices('Body Care')">Body Care</button>
                <button class="filter-btn" onclick="filterServices('Hair Care')">Hair Care</button>
                <button class="filter-btn" onclick="filterServices('Makeover')">Makeover</button>
                <button class="filter-btn" onclick="filterServices('Bridal')">Bridal</button>
                <!-- Add more categories as needed -->
            </div>

            <div class="service-grid">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="service-card" data-category="' . $row["category"] . '">';
                        echo '<h3>' . $row["sname"] . '</h3>';
                        echo '<p>Price: ' . $row["sprice"] . ' BDT</p>';
                        echo '<p>Duration: ' . $row["sduration"] . '</p>';
                        echo '<a href="book_appointment.php" class="btn">Book Appointment</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No services available</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        function filterServices(category) {
            const searchInput = document.getElementById('search').value.toLowerCase();
            const cards = document.querySelectorAll('.service-card');
            cards.forEach(card => {
                const serviceName = card.querySelector('h3').innerText.toLowerCase();
                if ((category === 'all' || card.getAttribute('data-category') === category) && serviceName.includes(searchInput)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>

<?php
$conn->close();
?>