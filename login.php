<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>User Login</title>
    <link href="../css/admin.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Poppins', sans-serif;
        }

        .card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .admin_label {
            font-weight: 600;
            color: #34495E;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #BDC3C7;
            box-shadow: none;
        }

        .form-control:focus {
            border-color: #3498DB;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
        }

        .btn-primary {
            background-color: #3498DB;
            border: none;
            font-weight: bold;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
        }

        .btn-primary:hover {
            background-color: #2980B9;
        }

        .btn-link {
            color: #3498DB;
            font-weight: 500;
        }

        .btn-link:hover {
            text-decoration: underline;
        }

        .card-body {
            background-color: #ecf0f1;
            border-radius: 10px;
        }

        .admin_form {
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <?php 
    require_once('incs/conn.php');
    session_start();

    if (isset($_POST['submit']) && $_POST['submit'] == 'sub') {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Query to fetch the user with the entered email
        $query = "SELECT * FROM `users` WHERE email='$email' AND role = 'user'";
        $result = mysqli_query($conn, $query);
        $total_records = mysqli_num_rows($result);
        $admin = mysqli_fetch_assoc($result);

        if ($total_records == 0) {
            $msg = 'Invalid Email';
        } else {
            if (password_verify($password, $admin['password'])) {
                // If the password is correct, start a session and store user information
                $_SESSION['id'] = $admin['id'];  
                $_SESSION['email'] = $admin['email'];
                $_SESSION['role'] = $admin['role'];
                
                header("location:index.php");
                exit;
            } else {
                $msg = 'Invalid login credentials';
            }
        }
    }
    ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-body p-5">
                        <h1 class="text-center mb-4" style="font-family: 'Poppins', sans-serif; color: #2C3E50;">User Login</h1>
                        <?php if (isset($msg)) echo '<p style="color:red; text-align:center;">' . $msg . '</p>'; ?>
                        <form action="" method="post" class="admin_form">
                            <div class="form-group mb-4">
                                <label for="username" class="admin_label">Email:</label>
                                <input type="email" class="form-control admin_username" id="username" name="email" value="<?= @$_POST['email'] ?>" placeholder="Enter your email" required>
                            </div>

                            <div class="form-group mb-4">
                                <label for="password" class="admin_label">Password:</label>
                                <input type="password" class="form-control admin_password" id="password" name="password" value="<?= @$_POST['password'] ?>" placeholder="Enter your password" required>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block" name="submit" value="sub">Login</button>

                            <div class="d-flex justify-content-center">
                                <a href="index.php" class="btn btn-link text-decoration-none text-dark">Go to Home</a>
                                <a href="survey.php" class="btn btn-link text-decoration-none text-dark">Go to Survey</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $(".admin_form").on("submit", function (e) {
                var email = $("#username").val();
                var password = $("#password").val();

                if (email === "" || password === "") {
                    e.preventDefault();
                    alert("Please fill in both fields.");
                }
            });
        });
    </script>

</body>

</html>
