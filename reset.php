

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- SweetAlert2 CDN -->


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.24/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.24/dist/sweetalert2.min.js"></script>
    <title>Email Verification</title>
    <style>
        /* Global styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Form container */
        .form-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            text-align: center;
        }

        .form-container h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }

        input[type="email"] {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px 15px;
            margin-top: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: #f44336;
            font-size: 14px;
            margin-top: 10px;
        }

        .success-message {
            color: #4caf50;
            font-size: 14px;
            margin-top: 10px;
        }

        /* Responsive design */
        @media (max-width: 500px) {
            .form-container {
                padding: 30px;
                width: 90%;
            }
        }
    </style>
</head>
<body>

    <div class="form-container">
        <h2>Enter your Email for Verification</h2>
        
        <!-- Form for entering email -->
        <form action="" method="POST" onsubmit="return validateEmail();">
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
            <input type="submit" value="Check Email">
        </form>

        <!-- Error message display -->
        <div id="error-message" class="error-message"></div>

        <?php
            // If error message exists in the URL, display it
            if (isset($_GET['error'])) {
                echo "<p class='error-message'>".$_GET['error']."</p>";
            }
        ?>
    </div>

    <script>
        // JavaScript for form validation before submission
        function validateEmail() {
            var email = document.getElementById("email").value;
            var errorMessage = document.getElementById("error-message");

            // Simple email validation pattern
            var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;

            if (!emailPattern.test(email)) {
                errorMessage.textContent = "Please enter a valid email address.";
                return false;  // Prevent form submission
            } else {
                errorMessage.textContent = "";  // Clear any previous error
                return true;  // Allow form submission
            }
        }

        // Display the PHP-generated JavaScript alert if set
        <?php
            if ($script) {
                echo $script;
            }
        ?>
    </script>
    <?php
require_once('incs/conn.php');

session_start(); // Start the session at the top of the script

$script = ''; // Initialize variable to store JavaScript

// Initialize session flags
if (!isset($_SESSION['email_checked'])) {
    $_SESSION['email_checked'] = false;
}

if (isset($_POST['email'])) {
    $email = $_POST['email'];

    // Assuming $conn is the MySQLi connection
    // Prepare SQL query to check if email exists in the database
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error); // Error checking for prepare statement
    }

    // Bind parameters and execute
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the email exists in the database
    if ($result->num_rows > 0) {
        // Email found, check if verified
        $row = $result->fetch_assoc();
        if ($row['email_verification'] == 0) {
            // Email not verified
            $_SESSION['email_checked'] = true;
            $_SESSION['email_status'] = 'error';
            $_SESSION['email_message'] = 'Email not verified';
        } elseif ($row['email_verification'] == 1) {
            // Email found and verified
            $_SESSION['email_checked'] = true;
            $_SESSION['email_status'] = 'success';
            $_SESSION['email_message'] = 'Email found and verified';
        }
    } else {
        // Email not found, set session flag for error and redirect
        $_SESSION['email_checked'] = true;
        $_SESSION['email_status'] = 'error';
        $_SESSION['email_message'] = 'Email not found. Please try again with a valid email';
    }

    $stmt->close();
    $conn->close();

    // Redirect to avoid resubmission of form
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Show the appropriate Swal alert based on session flags
if (isset($_SESSION['email_checked']) && $_SESSION['email_checked'] === true) {
    $status = $_SESSION['email_status'];
    $message = $_SESSION['email_message'];
    echo "<script>
            Swal.fire({
                title: '" . ucfirst($status) . "',
                text: '$message',
                icon: '$status',
                confirmButtonText: 'OK'
            });
          </script>";

    // Unset session flags after alert is shown
    unset($_SESSION['email_checked']);
    unset($_SESSION['email_status']);
    unset($_SESSION['email_message']);
}
?>


</body>
</html>
