<?php include 'includes/header.php'; ?>
<?php include 'includes/db_connect.php'; ?>

<div class="content">
    <main>
        <h1>Our Staff</h1>
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search staff by category...">
            <button type="submit">Search</button>
        </form>
        <ul>
            <?php
            $search = $_GET['search'] ?? '';
            $sql = "SELECT * FROM staff WHERE staff_role LIKE '%$search%'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<li>{$row['staff_name']} - {$row['staff_role']}</li>";
                }
            } else {
                echo "<li>No staff found</li>";
            }
            ?>
        </ul>
    </main>
</div>

<?php include 'includes/footer.php'; ?>