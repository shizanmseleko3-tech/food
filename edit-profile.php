<?php
session_start();
include('config/constants.php');

// Redirect if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION['u_id'];
$showAlert = false;
$showError = "";

// Fetch current user data
$stmt = mysqli_prepare($conn, "SELECT username, customer_name, customer_email, customer_contact, customer_address FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $cur_username, $cur_name, $cur_email, $cur_contact, $cur_address);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// On form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST["username"]) ? strtolower(trim($_POST["username"])) : '';
    $customer_name = isset($_POST["customer_name"]) ? trim($_POST["customer_name"]) : '';
    $customer_email = isset($_POST["customer_email"]) ? strtolower(trim($_POST["customer_email"])) : '';
    $customer_contact = isset($_POST["customer_contact"]) ? trim($_POST["customer_contact"]) : '';
    $customer_address = isset($_POST["customer_address"]) ? trim($_POST["customer_address"]) : '';
    $password = isset($_POST["password"]) ? trim($_POST["password"]) : '';
    $cpassword = isset($_POST["cpassword"]) ? trim($_POST["cpassword"]) : '';

    // Validation
    if (!preg_match('/^[a-z0-9_]{3,20}$/', $username)) {
        $showError = "Username must be 3–20 chars, lowercase letters, numbers, or underscores.";
    } elseif (!preg_match("/^[a-zA-Z ]{2,50}$/", $customer_name)) {
        $showError = "Full name must be letters and spaces only (2–50 chars).";
    } elseif (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $showError = "Invalid email address.";
    } elseif (preg_match('/[A-Z]/', $customer_email)) {
        $showError = "Email must be lowercase only.";
    } elseif (!preg_match('/^\d{10}$/', $customer_contact)) {
        $showError = "Phone number must be exactly 10 digits.";
    } elseif (empty($customer_address)) {
        $showError = "Address cannot be empty.";
    } elseif (!empty($password) && strlen($password) < 6) {
        $showError = "Password must be at least 6 characters.";
    } elseif (!empty($password) && $password !== $cpassword) {
        $showError = "Passwords do not match.";
    } else {
        // Check if username is taken by another user
        $stmt_check = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? AND id != ?");
        mysqli_stmt_bind_param($stmt_check, "si", $username, $user_id);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $showError = "Username already taken.";
        } else {
            // Update query
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt_update = mysqli_prepare($conn, "UPDATE users SET username=?, customer_name=?, customer_email=?, customer_contact=?, customer_address=?, password=? WHERE id=?");
                mysqli_stmt_bind_param($stmt_update, "ssssssi", $username, $customer_name, $customer_email, $customer_contact, $customer_address, $hash, $user_id);
            } else {
                $stmt_update = mysqli_prepare($conn, "UPDATE users SET username=?, customer_name=?, customer_email=?, customer_contact=?, customer_address=? WHERE id=?");
                mysqli_stmt_bind_param($stmt_update, "sssssi", $username, $customer_name, $customer_email, $customer_contact, $customer_address, $user_id);
            }

            if (mysqli_stmt_execute($stmt_update)) {
                $showAlert = true;

                // Update session username if changed
                $_SESSION['username'] = $username;

                // Refresh current data variables for form display
                $cur_username = $username;
                $cur_name = $customer_name;
                $cur_email = $customer_email;
                $cur_contact = $customer_contact;
                $cur_address = $customer_address;
            } else {
                $showError = "Failed to update profile. Please try again.";
            }

            mysqli_stmt_close($stmt_update);
        }
        mysqli_stmt_close($stmt_check);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" />
</head>
<body>
<div class="container my-4">
    <h2>Edit Your Profile</h2>

    <?php if ($showAlert): ?>
        <div class="alert alert-success">Profile updated successfully!</div>
    <?php elseif (!empty($showError)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($showError) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="form-group">
            <label>Username (lowercase, 3-20 chars)</label>
            <input type="text" name="username" class="form-control" required
                pattern="[a-z0-9_]{3,20}" value="<?= htmlspecialchars($cur_username) ?>" />
        </div>

        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="customer_name" class="form-control" required
                pattern="[a-zA-Z ]{2,50}" value="<?= htmlspecialchars($cur_name) ?>" />
        </div>

        <div class="form-group">
            <label>Email (lowercase only)</label>
            <input type="email" name="customer_email" class="form-control" required
                pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" value="<?= htmlspecialchars($cur_email) ?>" />
        </div>

        <div class="form-group">
            <label>Phone (10 digits)</label>
            <input type="text" name="customer_contact" class="form-control" required pattern="\d{10}" value="<?= htmlspecialchars($cur_contact) ?>" />
        </div>

        <div class="form-group">
            <label>Address</label>
            <textarea name="customer_address" class="form-control" required><?= htmlspecialchars($cur_address) ?></textarea>
        </div>

        <div class="form-group">
            <label>New Password (leave blank to keep current)</label>
            <input type="password" name="password" class="form-control" minlength="6" />
        </div>

        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="cpassword" class="form-control" minlength="6" />
        </div>

        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
