<?php
session_start();
include('config/constants.php');

$showAlert = false;
$showError = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST["username"]) ? strtolower(trim($_POST["username"])) : '';
    $password = isset($_POST["password"]) ? trim($_POST["password"]) : '';
    $cpassword = isset($_POST["cpassword"]) ? trim($_POST["cpassword"]) : '';
    $customer_name = isset($_POST["customer_name"]) ? trim($_POST["customer_name"]) : '';
    $customer_email = isset($_POST["customer_email"]) ? strtolower(trim($_POST["customer_email"])) : '';
    $customer_contact = isset($_POST["customer_contact"]) ? trim($_POST["customer_contact"]) : '';
    $customer_address = isset($_POST["customer_address"]) ? trim($_POST["customer_address"]) : '';

    if (!preg_match('/^[a-z0-9_]{3,20}$/', $username)) {
        $showError = "Username must be 3–20 characters long and contain only lowercase letters, numbers, or underscores.";
    } elseif (!preg_match("/^[a-zA-Z ]{2,50}$/", $customer_name)) {
        $showError = "Full name must contain only letters and spaces (2–50 characters).";
    } elseif (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $showError = "Please enter a valid email address.";
    } elseif (preg_match('/[A-Z]/', $_POST["customer_email"])) {
        $showError = "Email must contain lowercase letters only.";
    } elseif (!preg_match('/^\d{10}$/', $customer_contact)) {
        $showError = "Phone number must be exactly 10 digits.";
    } elseif (strlen($password) < 6) {
        $showError = "Password must be at least 6 characters long.";
    } elseif ($password !== $cpassword) {
        $showError = "Passwords do not match.";
    } elseif (empty($customer_address)) {
        $showError = "Address cannot be empty.";
    } else {
        $stmt_check = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt_check, "s", $username);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $showError = "Username already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert = mysqli_prepare($conn, "INSERT INTO users (username, password, customer_name, customer_email, customer_contact, customer_address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            mysqli_stmt_bind_param($stmt_insert, "ssssss", $username, $hash, $customer_name, $customer_email, $customer_contact, $customer_address);

            if (mysqli_stmt_execute($stmt_insert)) {
                $showAlert = true;
            } else {
                $showError = "Registration failed. Please try again.";
            }

            mysqli_stmt_close($stmt_insert);
        }

        mysqli_stmt_close($stmt_check);
    }

    mysqli_close($conn);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Food Order</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body>

<!-- Navbar -->
<section class="navbar">
    <div class="container">
        <div class="logo">
            <a href="index.php">
                <img src="images/logo.png" alt="Logo" style="max-height: 50px;">
            </a>
        </div>
        <div class="clearfix"></div>
    </div>
</section>

<!-- Registration Form -->
<div class="container my-4">
    <h2 class="text-center">Signup Here</h2>
    <h5>*All fields are required</h5>

    <?php if ($showAlert): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> Your account has been created. <a href="login.php">Login here</a>.
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php elseif (!empty($showError)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong> <?= $showError ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <form action="" method="post">
        <div class="form-group">
            <label>Username (lowercase, 3–20 chars)</label>
            <input type="text" name="username" class="form-control" required pattern="[a-z0-9_]{3,20}" title="3-20 lowercase letters, numbers, or underscores">
        </div>

        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="customer_name" class="form-control" required pattern="[a-zA-Z ]{2,50}" title="Only letters and spaces (2–50 chars)">
        </div>

        <div class="form-group">
            <label>Email (lowercase only)</label>
            <input type="email" name="customer_email" class="form-control"
                   required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                   title="Email must be lowercase only (e.g. example@domain.com)">
        </div>

        <div class="form-group">
            <label>Phone (10 digits)</label>
            <input type="text" name="customer_contact" class="form-control" required pattern="\d{10}" title="10-digit phone number">
        </div>

        <div class="form-group">
            <label>Address</label>
            <textarea name="customer_address" class="form-control" required></textarea>
        </div>

        <div class="form-group">
            <label>Password (min 6 characters)</label>
            <input type="password" name="password" class="form-control" required minlength="6">
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="cpassword" class="form-control" required minlength="6">
        </div>

        <button type="submit" class="btn btn-primary">Sign Up</button>
    </form>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Footer Section Starts -->
<!-- <section class="footer mt-5"> -->
    <!-- <div class="container text-center"> -->
        <!-- <p>All rights reserved. Designed by <a href="#">YourName</a></p> -->
        <!-- <div class="social d-flex justify-content-center align-items-center mt-2" style="gap: 15px;"> -->
            <!-- <a href="#"><img src="images/facebook.png" alt="Facebook" style="height: 30px;"></a> -->
            <!-- <a href="#"><img src="images/twitter.png" alt="Twitter" style="height: 30px;"></a> -->
            <!-- <a href="#"><img src="images/youtube.png" alt="YouTube" style="height: 30px;"></a> -->
        <!-- </div> -->
    <!-- </div> -->
<!-- </section> -->
<!-- Footer Section Ends -->

    <?php include('partials-front/footer.php'); ?>
</body>
</html>
