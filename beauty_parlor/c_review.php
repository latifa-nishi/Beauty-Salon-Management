<?php include 'includes/header.php'; ?>
<?php include 'includes/db_connect.php'; ?>

<div class="content">
    <main>
        <h1>Give a Review</h1>
        <?php
        // Check if the user is logged in
        if (isset($_SESSION['user_id'])) {
            // Fetch customer details based on the logged-in user ID
            $user_id = $_SESSION['user_id'];
            $sql = "SELECT cust_name, cust_id FROM customer WHERE cust_id='$user_id'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $cust_name = $row['cust_name'];
                $cust_id = $row['cust_id'];
            } else {
                echo "<p class='error-message'>No customer found with the provided ID.</p>";
                exit;
            }
        } else {
            echo "<p class='error-message'>You need to log in to give a review.</p>";
            exit;
        }
        ?>
        <form method="POST" action="review.php">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($cust_name); ?>" readonly>
            
            <label for="rating">Rating (1-5):</label>
            <input type="number" id="rating" name="rating" min="1" max="5" required>
            
            <label for="comment">Comment:</label>
            <textarea id="comment" name="comment" required></textarea>
            
            <button type="submit">Submit Review</button>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $rating = $_POST['rating'];
            $comment = $_POST['comment'];

            $rev_id = uniqid('R');
            $sql = "INSERT INTO review (rev_id, cust_id, rating, comment) VALUES ('$rev_id', '$cust_id', '$rating', '$comment')";
            if ($conn->query($sql) === TRUE) {
                echo "<p class='success-message'>Review submitted successfully!</p>";
            } else {
                echo "<p class='error-message'>Error: " . $sql . "<br>" . $conn->error . "</p>";
            }
        }
        ?>
    </main>
</div>
<?php include 'includes/footer.php'; ?>