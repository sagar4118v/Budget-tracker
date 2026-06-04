<?php
require_once '../config.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Get user by email
    $stmt = $conn->prepare("
        SELECT * 
        FROM users 
        WHERE email = ? AND is_active = 1
    ");

    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check user exists
    if ($user) {

        // Direct password check
        // (use password_verify later if using hashed passwords)

        if ($password == $user['password']) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Redirect by role
            if ($user['role'] === 'admin') {

                header("Location: ../admin_dashboard.php");
                exit();

            } else {

                header("Location: ../dashboard.php");
                exit();
            }

        } else {

            $error = "Wrong password";
        }

    } else {

        $error = "User not found or inactive";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Budget Tracker</title>
    <link rel="icon" type="image/x-icon" href="logo/logo.png">

    <style>

        body{
            font-family: Arial;
            background:#f4f4f4;
        }

        .login-box{
            width:350px;
            margin:100px auto;
            background:white;
            padding:30px;
            border-radius:10px;
            box-shadow:0 0 10px rgba(0,0,0,0.1);
        }

        input{
            width:100%;
            padding:10px;
            margin-top:10px;
        }

        button{
            width:100%;
            padding:10px;
            margin-top:15px;
            background:#1e293b;
            color:white;
            border:none;
            cursor:pointer;
        }

        .error{
            color:red;
            margin-top:10px;
        }

    </style>
</head>

<body>

<div class="login-box">

    <h2>Login</h2>

    <?php if($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST">

        <input 
            type="email" 
            name="email" 
            placeholder="Email"
            required
        >

        <input 
            type="password" 
            name="password" 
            placeholder="Password"
            required
        >

        <button type="submit">
            Login
        </button>
        <br><br>

        <a href="../register/register.php">Don't have an account? Register  </a>

    </form>

</div>

</body>
</html>