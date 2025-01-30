<?php
session_start();

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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cust_name = $_POST['cust_name'];
    $cust_phone = $_POST['cust_phone'];
    $cust_email = $_POST['cust_mail'];  // Corrected variable name
    $cust_loc = $_POST['cust_loc'];     // Corrected variable name
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Generate customer ID
        $cust_query = "SELECT MAX(CAST(SUBSTRING(cust_id, 2) AS UNSIGNED)) as max_id FROM customer";
        $result = $conn->query($cust_query);
        $row = $result->fetch_assoc();
        $next_id = $row['max_id'] + 1;
        $cust_id = 'C' . str_pad($next_id, 3, '0', STR_PAD_LEFT);
        
        // Insert customer
        $stmt = $conn->prepare("INSERT INTO customer (cust_id, cust_name, cust_phone, cust_mail, cust_loc) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $cust_id, $cust_name, $cust_phone, $cust_email, $cust_loc);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        $success_message = "<p class='success-message'>Signup successful! Your Customer ID is $cust_id</p>";
        $redirect_message = "<p class='redirect-message'>Please wait, directing to login page...</p>";
        echo "<script>
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 3000); // Redirect after 3 seconds
              </script>";
    } catch (Exception $e) {
        // Rollback in case of error
        $conn->rollback();
        $error_message = "<div class='error-message'>Error creating account: " . $e->getMessage() . "</div>";
    }
    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Signup</title>
    <style>
        body { 
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .signup-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .signup-form h2 {
            margin-bottom: 20px;
            font-size: 24px;
            text-align: center;
        }
        .signup-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .signup-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .signup-form button {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .signup-form button:hover {
            background-color: #0056b3;
        }
        .success-message {
            margin-top: 20px;
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            text-align: center;
        }
        .redirect-message {
            margin-top: 10px;
            padding: 10px;
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            border-radius: 5px;
            text-align: center;
        }
        .error-message {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8d7da;
            color: #721c24;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <form class="signup-form" method="POST" action="">
        <h2>Customer Signup</h2>
        
        <label for="cust_name">Full Name</label>
        <input type="text" id="cust_name" name="cust_name" required>

        <label for="cust_phone">Phone Number</label>
        <input type="number" id="cust_phone" name="cust_phone" required>

        <label for="cust_mail">Email</label>
        <input type="email" id="cust_mail" name="cust_mail" required>

        <label for="cust_loc">Location</label>
        <input type="text" id="cust_loc" name="cust_loc" required>

        <button type="submit">Sign Up</button>
        
        <?php
        if (isset($success_message)) {
            echo $success_message;
            echo $redirect_message;
        } elseif (isset($error_message)) {
            echo $error_message;
        }
        ?>
    </form>
</body>
</html>
