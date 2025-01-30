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

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $login_input = mysqli_real_escape_string($conn, $_POST['login_input']);

    // Check if input is an email or phone number
    if (filter_var($login_input, FILTER_VALIDATE_EMAIL)) {
        // Input is an email
        $sql_admin = "SELECT * FROM admin WHERE admin_email='$login_input'";
        $sql_customer = "SELECT * FROM customer WHERE cust_mail='$login_input'";
        $sql_staff = "SELECT * FROM staff WHERE staff_mail='$login_input'";
    } else {
        // Input is a phone number
        $sql_admin = "SELECT * FROM admin WHERE admin_phone='$login_input'";
        $sql_customer = "SELECT * FROM customer WHERE cust_phone='$login_input'";
        $sql_staff = "SELECT * FROM staff WHERE staff_phone='$login_input'";
    }

    // Check admin table
    $result_admin = $conn->query($sql_admin);
    if ($result_admin->num_rows > 0) {
        $_SESSION['user_type'] = 'admin';
        $_SESSION['user_id'] = $result_admin->fetch_assoc()['admin_id'];
        header("Location: admin_dashboard.php");
        exit();
    }

    // Check customer table
    $result_customer = $conn->query($sql_customer);
    if ($result_customer->num_rows > 0) {
        $customer = $result_customer->fetch_assoc();
        $_SESSION['user_type'] = 'customer';
        $_SESSION['user_id'] = $customer['cust_id'];
        $_SESSION['user_name'] = $customer['cust_name'];
        $_SESSION['user_phone'] = $customer['cust_phone'];
        $_SESSION['user_email'] = $customer['cust_mail'];
        header("Location: home.php");
        exit();
    }

    // Check staff table
    $result_staff = $conn->query($sql_staff);
    if ($result_staff->num_rows > 0) {
        $staff = $result_staff->fetch_assoc();
        $_SESSION['user_type'] = 'staff';
        $_SESSION['user_id'] = $staff['staff_id'];

        // Redirect based on staff designation
        if ($staff['staff_role'] == 'Receptionist') {
            header("Location: receptionist_dashboard.php");
        } else {
            header("Location: staff_dashboard.php");
        }
        exit();
    }
    // If no match is found
    $error_message = "Invalid email or phone number. Please try again.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login</title>
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
        .login-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-form h2 {
            margin-bottom: 20px;
            font-size: 24px;
            text-align: center;
        }
        .login-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .login-form input {
            width: calc(100% - 22px); /* Adjust width to fit padding and border */
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .login-form button {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .login-form button:hover {
            background-color: #0056b3;
        }
        .signup-link {
            text-align: center;
            margin-top: 10px;
        }
        .signup-link a {
            color: #007BFF;
            text-decoration: none;
        }
        .signup-link a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: red;
            font-weight: bold;
            text-align: center; /* Center align the error message */
        }
    </style>
</head>
<body>
    <form class="login-form" method="POST" action="">
        <h2>Login</h2>
        <label for="login_input">Email or Phone Number</label>
        <input type="text" id="login_input" name="login_input" required>

        <button type="submit">Login</button>

        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <div class="signup-link">
            <p>Don't have an account? <a href="signup.php">Sign up</a></p>
        </div>
    </form>
</body>
</html>